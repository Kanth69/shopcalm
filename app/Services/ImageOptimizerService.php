<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageOptimizerService
{
    /**
     * Maximum dimension for product images (width or height).
     */
    public const DEFAULT_MAX_DIMENSION = 1200;

    /**
     * WebP compression quality (0-100). 82 is the optimal balance of crisp clarity and tiny file size.
     */
    public const DEFAULT_QUALITY = 82;

    /**
     * Optimize an uploaded file into WebP and store it on the public disk.
     *
     * @param UploadedFile $file The uploaded file
     * @param string $folder Target directory inside storage/app/public (e.g. 'products/main')
     * @param int $maxDimension Maximum width or height in pixels
     * @param int $quality Compression quality (1-100)
     * @return string Relative path in storage (e.g. 'products/main/abc123xyz.webp')
     */
    public function optimizeAndStore(
        UploadedFile $file,
        string $folder = 'products',
        int $maxDimension = self::DEFAULT_MAX_DIMENSION,
        int $quality = self::DEFAULT_QUALITY
    ): string {
        // Fallback to normal store if GD or WebP is not supported
        if (!extension_loaded('gd') || !function_exists('imagewebp')) {
            Log::warning('GD or imagewebp not available. Storing original image without optimization.');
            return $file->store($folder, 'public');
        }

        try {
            $realPath = $file->getRealPath();
            $mimeType = $file->getMimeType();

            $image = $this->createImageFromFile($realPath, $mimeType);
            if (!$image) {
                // If GD cannot parse format, store original safely
                return $file->store($folder, 'public');
            }

            // Correct EXIF orientation for JPEG camera uploads
            $image = $this->correctOrientation($image, $realPath, $mimeType);

            // Resize if dimensions exceed max dimension
            $optimizedImage = $this->resizeIfNeeded($image, $maxDimension);
            if ($optimizedImage !== $image) {
                imagedestroy($image);
            }

            // Encode to WebP buffer
            ob_start();
            imagewebp($optimizedImage, null, $quality);
            $webpData = ob_get_clean();
            imagedestroy($optimizedImage);

            if ($webpData === false || strlen($webpData) === 0) {
                return $file->store($folder, 'public');
            }

            // Save to public storage disk
            $filename = Str::random(40) . '.webp';
            $targetPath = trim($folder, '/') . '/' . $filename;
            Storage::disk('public')->put($targetPath, $webpData);

            return $targetPath;
        } catch (\Throwable $e) {
            Log::error('ImageOptimizerService failed: ' . $e->getMessage(), [
                'exception' => $e,
                'file'      => $file->getClientOriginalName(),
            ]);
            return $file->store($folder, 'public');
        }
    }

    /**
     * Optimize an existing file in public storage to WebP format.
     *
     * @param string $relativePath Path relative to public storage disk (e.g. 'products/main/photo.jpg')
     * @param int $maxDimension Maximum dimension in pixels
     * @param int $quality Compression quality (1-100)
     * @param bool $deleteOriginal Whether to delete original file after conversion
     * @return array|null Returns conversion stats or null on failure/skip
     */
    public function optimizeExistingStorageFile(
        string $relativePath,
        int $maxDimension = self::DEFAULT_MAX_DIMENSION,
        int $quality = self::DEFAULT_QUALITY,
        bool $deleteOriginal = true
    ): ?array {
        $disk = Storage::disk('public');
        if (!$disk->exists($relativePath)) {
            return null;
        }

        $fullPath = $disk->path($relativePath);
        $originalSize = @filesize($fullPath) ?: 0;

        // If already webp and reasonably sized, skip
        $extension = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));
        if ($extension === 'webp' && $originalSize > 0 && $originalSize <= 50 * 1024) {
            return null;
        }

        if (!extension_loaded('gd') || !function_exists('imagewebp')) {
            return null;
        }

        try {
            $mimeType = @mime_content_type($fullPath) ?: 'image/jpeg';
            $image = $this->createImageFromFile($fullPath, $mimeType);
            if (!$image) {
                return null;
            }

            $image = $this->correctOrientation($image, $fullPath, $mimeType);
            $optimizedImage = $this->resizeIfNeeded($image, $maxDimension);
            if ($optimizedImage !== $image) {
                imagedestroy($image);
            }

            ob_start();
            imagewebp($optimizedImage, null, $quality);
            $webpData = ob_get_clean();
            imagedestroy($optimizedImage);

            if ($webpData === false || strlen($webpData) === 0) {
                return null;
            }

            // Determine target path
            $dir = dirname($relativePath);
            $filenameWithoutExt = pathinfo($relativePath, PATHINFO_FILENAME);
            $targetPath = ($dir === '.' ? '' : $dir . '/') . $filenameWithoutExt . '.webp';

            // If same path, temporarily write then replace
            $disk->put($targetPath, $webpData);
            $newSize = strlen($webpData);

            if ($deleteOriginal && $targetPath !== $relativePath && $disk->exists($relativePath)) {
                $disk->delete($relativePath);
            }

            return [
                'old_path'      => $relativePath,
                'new_path'      => $targetPath,
                'original_size' => $originalSize,
                'new_size'      => $newSize,
                'saved_bytes'   => max(0, $originalSize - $newSize),
            ];
        } catch (\Throwable $e) {
            Log::error('optimizeExistingStorageFile failed for ' . $relativePath . ': ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create GD image resource based on file MIME type / extension.
     */
    protected function createImageFromFile(string $path, ?string $mimeType)
    {
        $mime = strtolower((string) $mimeType);

        if (str_contains($mime, 'jpeg') || str_contains($mime, 'jpg')) {
            return @imagecreatefromjpeg($path);
        }
        if (str_contains($mime, 'png')) {
            return @imagecreatefrompng($path);
        }
        if (str_contains($mime, 'webp')) {
            return @imagecreatefromwebp($path);
        }
        if (str_contains($mime, 'gif')) {
            return @imagecreatefromgif($path);
        }

        // Extension fallback
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return match ($ext) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($path),
            'png'         => @imagecreatefrompng($path),
            'webp'        => @imagecreatefromwebp($path),
            'gif'         => @imagecreatefromgif($path),
            default       => null,
        };
    }

    /**
     * Auto-correct orientation based on EXIF tag.
     */
    protected function correctOrientation($image, string $path, ?string $mimeType)
    {
        if (!function_exists('exif_read_data') || !function_exists('imagerotate')) {
            return $image;
        }

        $mime = strtolower((string) $mimeType);
        if (!str_contains($mime, 'jpeg') && !str_contains($mime, 'jpg')) {
            return $image;
        }

        try {
            $exif = @exif_read_data($path);
            if (!empty($exif['Orientation'])) {
                switch ($exif['Orientation']) {
                    case 3:
                        $rotated = imagerotate($image, 180, 0);
                        imagedestroy($image);
                        return $rotated;
                    case 6:
                        $rotated = imagerotate($image, -90, 0);
                        imagedestroy($image);
                        return $rotated;
                    case 8:
                        $rotated = imagerotate($image, 90, 0);
                        imagedestroy($image);
                        return $rotated;
                }
            }
        } catch (\Throwable $e) {
            // Ignore EXIF read errors
        }

        return $image;
    }

    /**
     * Resize image if width or height exceeds maximum dimension while keeping aspect ratio.
     */
    protected function resizeIfNeeded($image, int $maxDimension)
    {
        $origWidth = imagesx($image);
        $origHeight = imagesy($image);

        if ($origWidth <= 0 || $origHeight <= 0) {
            return $image;
        }

        // If image is already smaller than or equal to max dimension, no need to resize
        if ($origWidth <= $maxDimension && $origHeight <= $maxDimension) {
            return $image;
        }

        // Calculate proportional scale
        $scale = min($maxDimension / $origWidth, $maxDimension / $origHeight);
        $newWidth = (int) max(1, round($origWidth * $scale));
        $newHeight = (int) max(1, round($origHeight * $scale));

        $target = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve alpha transparency
        imagealphablending($target, false);
        imagesavealpha($target, true);
        $transparent = imagecolorallocatealpha($target, 255, 255, 255, 127);
        imagefilledrectangle($target, 0, 0, $newWidth, $newHeight, $transparent);

        // Resample with smooth bicubic interpolation
        imagecopyresampled($target, $image, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

        return $target;
    }
}
