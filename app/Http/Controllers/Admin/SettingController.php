<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $user = auth('admin')->user() ?? auth()->user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized. Super Admin access required for Store Settings.');
        }

        $settings = Setting::getAll();
        $brevoQuota = app(\App\Services\EmailService::class)->getAccountQuota();

        return view('admin.settings.index', compact('settings', 'brevoQuota'));
    }

    public function update(Request $request)
    {
        $user = auth('admin')->user() ?? auth()->user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized. Super Admin access required for Store Settings.');
        }

        // Check if PHP rejected the uploaded file before validation (e.g. exceeds upload_max_filesize)
        foreach (['logo' => 'Main Storefront Logo', 'favicon' => 'Browser Tab Favicon'] as $fileKey => $label) {
            if (isset($_FILES[$fileKey]) && ($_FILES[$fileKey]['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK && ($_FILES[$fileKey]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $errCode = $_FILES[$fileKey]['error'];
                $errMsg = match ($errCode) {
                    UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => "{$label} upload failed: The selected file exceeds the server maximum upload size limit (" . ini_get('upload_max_filesize') . "). Please choose a smaller image (under 5 MB).",
                    UPLOAD_ERR_PARTIAL => "{$label} upload failed: The file was only partially uploaded. Please try again.",
                    default => "{$label} upload failed (PHP upload error code {$errCode}). Please try another image file.",
                };
                return back()->withInput()->withErrors([$fileKey => $errMsg])->with('error', $errMsg);
            }
        }

        $validated = $request->validate([
            'store_name'                  => 'nullable|string|max:255',
            'tagline'                     => 'nullable|string|max:500',
            'copyright_text'              => 'nullable|string|max:255',
            'support_hours'               => 'nullable|string|max:255',
            'contact_email'               => 'nullable|email|max:255',
            'contact_phone'               => 'nullable|string|max:20',
            'whatsapp_number'             => 'nullable|string|max:20',
            'address'                     => 'nullable|string|max:500',
            'currency'                    => 'nullable|string|max:10',
            'currency_symbol'             => 'nullable|string|max:5',
            'logo'                        => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp,ico,bmp,jfif,avif|max:10240',
            'favicon'                     => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp,ico,bmp,jfif,avif|max:5120',
            'enable_trust_badges'         => 'nullable|string',
            'free_shipping_min'           => 'nullable|numeric',
            'cod_fee_enabled'             => 'nullable|string',
            'cod_flat_fee'                => 'nullable|numeric|min:0',
            'enable_flash_sale'           => 'nullable|string',
            'flash_sale_end_time'         => 'nullable|string',
            'flash_sale_title'            => 'nullable|string|max:255',
            'trust_badge_1_title'         => 'nullable|string|max:100',
            'trust_badge_1_desc'          => 'nullable|string|max:150',
            'trust_badge_2_title'         => 'nullable|string|max:100',
            'trust_badge_2_desc'          => 'nullable|string|max:150',
            'trust_badge_3_title'         => 'nullable|string|max:100',
            'trust_badge_3_desc'          => 'nullable|string|max:150',
            'trust_badge_4_title'         => 'nullable|string|max:100',
            'trust_badge_4_desc'          => 'nullable|string|max:150',
            'allow_customer_cancellation' => 'nullable|string',
            'facebook_url'                => 'nullable|url|max:255',
            'instagram_url'               => 'nullable|url|max:255',
            'twitter_url'                 => 'nullable|url|max:255',
            'linkedin_url'                => 'nullable|url|max:255',
            'youtube_url'                 => 'nullable|url|max:255',
            'whatsapp_api_token'          => 'nullable|string|max:500',
            'whatsapp_phone_number_id'    => 'nullable|string|max:255',
            'whatsapp_template_name'      => 'nullable|string|max:255',
            'brevo_api_key'               => 'nullable|string|max:255',
            'brevo_sender_email'          => 'nullable|email|max:255',
            'brevo_sender_name'           => 'nullable|string|max:255',
            'razorpay_key_id'             => 'nullable|string|max:255',
            'razorpay_key_secret'         => 'nullable|string|max:255',
        ], [
            'favicon.mimes' => 'Browser Tab Favicon must be a valid image or icon file (PNG, JPG, JPEG, ICO, WEBP, SVG, or GIF).',
            'favicon.max'   => 'Browser Tab Favicon is too large (:max KB max / 5 MB). Please upload an image under 5 MB.',
            'favicon.uploaded' => 'Browser Tab Favicon failed to upload. Please check that the file is under 5 MB and in PNG, JPG, ICO, WEBP, or SVG format.',
            'logo.mimes'    => 'Main Storefront Logo must be a valid image file (PNG, JPG, JPEG, SVG, WEBP, or GIF).',
            'logo.max'      => 'Main Storefront Logo is too large (maximum 10 MB allowed).',
            'logo.uploaded' => 'Main Storefront Logo failed to upload. Please check the file size (max 10 MB) and format.',
        ]);

        foreach ($validated as $key => $value) {
            if (in_array($key, ['logo', 'favicon'], true)) {
                if ($request->hasFile($key)) {
                    $oldValue = Setting::get($key);
                    if ($oldValue && Storage::disk('public')->exists($oldValue)) {
                        Storage::disk('public')->delete($oldValue);
                    }
                    $storedPath = $request->file($key)->store('settings', 'public');
                    Setting::set($key, $storedPath);

                    if ($key === 'favicon') {
                        try {
                            @copy(Storage::disk('public')->path($storedPath), public_path('favicon.ico'));
                            @copy(Storage::disk('public')->path($storedPath), public_path('favicon.png'));
                        } catch (\Throwable $e) {
                            // Ignore if public/favicon is not writable
                        }
                    }
                }
                continue;
            }
            Setting::set($key, $value);
        }

        return back()
            ->with('toast', ['type' => 'success', 'title' => 'Settings Saved', 'message' => 'Store settings updated successfully.'])
            ->with('success', 'Store settings updated successfully.');
    }

    /**
     * Real-Time AJAX endpoint to query Brevo credits & plan metrics.
     */
    public function getBrevoCredits()
    {
        $quota = app(\App\Services\EmailService::class)->getAccountQuota();
        return response()->json($quota);
    }

    /**
     * AJAX endpoint to trigger a Brevo REST API verification test email.
     */
    public function sendTestEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $result = app(\App\Services\EmailService::class)->sendTestEmail($request->email);

        if ($result['success'] ?? false) {
            return response()->json([
                'success' => true,
                'message' => "Verification test email sent to {$request->email} via " . strtoupper($result['driver'] ?? 'BREVO API') . "!",
                'driver'  => $result['driver'] ?? 'brevo',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => "Failed to dispatch test email. Error code: " . ($result['status'] ?? '500'),
        ], 422);
    }
}
