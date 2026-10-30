<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ImageOptimizerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ConvertImagesToWebp extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'images:convert-to-webp 
                            {--quality=82 : WebP quality from 1 to 100} 
                            {--max-dimension=1200 : Maximum width or height in px} 
                            {--keep-originals : Keep original image files after conversion} 
                            {--dry-run : Preview conversion without modifying database or files}';

    /**
     * The console command description.
     */
    protected $description = 'Convert all existing ShopCalm product photos (main, variants, gallery) to lightweight, high-fidelity WebP format (< 30-50 KB)';

    public function handle(ImageOptimizerService $optimizer): int
    {
        $this->info('===========================================================');
        $this->info('  ShopCalm - Automated WebP Image Optimizer & Converter   ');
        $this->info('===========================================================');

        $quality = (int) $this->option('quality');
        $maxDimension = (int) $this->option('max-dimension');
        $keepOriginals = (bool) $this->option('keep-originals');
        $dryRun = (bool) $this->option('dry-run');

        if (!extension_loaded('gd') || !function_exists('imagewebp')) {
            $this->error('Error: PHP GD extension with WebP support is required but not loaded.');
            return self::FAILURE;
        }

        $this->comment("Target Quality: {$quality}% | Max Dimension: {$maxDimension}px | Dry Run: " . ($dryRun ? 'YES' : 'NO'));
        $this->newLine();

        $disk = Storage::disk('public');
        $convertedCount = 0;
        $skippedCount = 0;
        $totalOriginalBytes = 0;
        $totalNewBytes = 0;

        // 1. Process Product Main Images
        $this->info('1/3 Optimizing Product Main Images...');
        $products = Product::whereNotNull('main_image')->get();

        foreach ($products as $product) {
            $relPath = $product->main_image;
            if (!$disk->exists($relPath)) {
                $skippedCount++;
                continue;
            }

            if ($dryRun) {
                $this->line("  [Dry-Run] Would convert product #{$product->id} main image: {$relPath}");
                continue;
            }

            $stats = $optimizer->optimizeExistingStorageFile($relPath, $maxDimension, $quality, !$keepOriginals);
            if ($stats) {
                $convertedCount++;
                $totalOriginalBytes += $stats['original_size'];
                $totalNewBytes += $stats['new_size'];

                if ($stats['new_path'] !== $relPath) {
                    $product->update(['main_image' => $stats['new_path']]);
                }

                $savedKb = round($stats['saved_bytes'] / 1024, 1);
                $origKb = round($stats['original_size'] / 1024, 1);
                $newKb = round($stats['new_size'] / 1024, 1);
                $this->line("  ✓ [Product #{$product->id}] {$origKb} KB → {$newKb} KB (saved {$savedKb} KB)");
            } else {
                $skippedCount++;
            }
        }

        // 2. Process Product Variant Option Images
        $this->newLine();
        $this->info('2/3 Optimizing Product Variant Images...');
        $variantProducts = Product::whereNotNull('option_images')->get();

        foreach ($variantProducts as $product) {
            $imagesMap = $product->option_images;
            if (!is_array($imagesMap) || empty($imagesMap)) {
                continue;
            }

            $updatedMap = $imagesMap;
            $hasChanges = false;

            foreach ($imagesMap as $key => $relPath) {
                if (empty($relPath) || !$disk->exists($relPath)) {
                    continue;
                }

                if ($dryRun) {
                    $this->line("  [Dry-Run] Would convert variant image for #{$product->id} [{$key}]: {$relPath}");
                    continue;
                }

                $stats = $optimizer->optimizeExistingStorageFile($relPath, $maxDimension, $quality, !$keepOriginals);
                if ($stats) {
                    $convertedCount++;
                    $totalOriginalBytes += $stats['original_size'];
                    $totalNewBytes += $stats['new_size'];

                    $updatedMap[$key] = $stats['new_path'];
                    $hasChanges = true;

                    $origKb = round($stats['original_size'] / 1024, 1);
                    $newKb = round($stats['new_size'] / 1024, 1);
                    $this->line("  ✓ [Variant #{$product->id} - {$key}] {$origKb} KB → {$newKb} KB");
                }
            }

            if ($hasChanges && !$dryRun) {
                $product->update(['option_images' => $updatedMap]);
            }
        }

        // 3. Process Product Gallery Images
        $this->newLine();
        $this->info('3/3 Optimizing Product Gallery Images...');
        $galleryImages = ProductImage::all();

        foreach ($galleryImages as $galleryImage) {
            $relPath = $galleryImage->image;
            if (!$disk->exists($relPath)) {
                $skippedCount++;
                continue;
            }

            if ($dryRun) {
                $this->line("  [Dry-Run] Would convert gallery image #{$galleryImage->id}: {$relPath}");
                continue;
            }

            $stats = $optimizer->optimizeExistingStorageFile($relPath, $maxDimension, $quality, !$keepOriginals);
            if ($stats) {
                $convertedCount++;
                $totalOriginalBytes += $stats['original_size'];
                $totalNewBytes += $stats['new_size'];

                if ($stats['new_path'] !== $relPath) {
                    $galleryImage->update(['image' => $stats['new_path']]);
                }

                $origKb = round($stats['original_size'] / 1024, 1);
                $newKb = round($stats['new_size'] / 1024, 1);
                $this->line("  ✓ [Gallery #{$galleryImage->id}] {$origKb} KB → {$newKb} KB");
            } else {
                $skippedCount++;
            }
        }

        // Clear storefront product caches
        if (!$dryRun) {
            Cache::forget('featured_products');
            Cache::forget('trending_products');
            Cache::forget('latest_products');
            Cache::forget('home_explore_products_p1');
            Cache::forget('home_total_active_products');
            Cache::forget('search_product_words');
        }

        // Summary Statistics
        $this->newLine();
        $this->info('===========================================================');
        $this->info('                 CONVERSION SUMMARY REPORT                 ');
        $this->info('===========================================================');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Images Converted to WebP', $convertedCount],
                ['Skipped / Already Optimized', $skippedCount],
                ['Original Size', round($totalOriginalBytes / (1024 * 1024), 2) . ' MB (' . round($totalOriginalBytes / 1024, 1) . ' KB)'],
                ['New WebP Size', round($totalNewBytes / (1024 * 1024), 2) . ' MB (' . round($totalNewBytes / 1024, 1) . ' KB)'],
                ['Total Disk Space Saved', round(($totalOriginalBytes - $totalNewBytes) / (1024 * 1024), 2) . ' MB (' . ($totalOriginalBytes > 0 ? round((($totalOriginalBytes - $totalNewBytes) / $totalOriginalBytes) * 100, 1) : 0) . '% reduction)'],
            ]
        );

        $this->info('✨ WebP optimization completed successfully! All product photos will now load blazing fast.');
        return self::SUCCESS;
    }
}
