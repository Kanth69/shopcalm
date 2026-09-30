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

        $settings = Setting::all()->pluck('value', 'key')->toArray();
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $user = auth('admin')->user() ?? auth()->user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized. Super Admin access required for Store Settings.');
        }

        $validated = $request->validate([
            'store_name' => 'nullable|string|max:255',
            'tagline' => 'nullable|string|max:500',
            'copyright_text' => 'nullable|string|max:255',
            'support_hours' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'whatsapp_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'currency' => 'nullable|string|max:10',
            'currency_symbol' => 'nullable|string|max:5',
            'logo' => 'nullable|image|max:2048',
            'favicon' => 'nullable|image|max:1024',
            'enable_trust_badges' => 'nullable|string',
            'free_shipping_min' => 'nullable|numeric',
            'cod_fee_enabled' => 'nullable|string',
            'cod_flat_fee' => 'nullable|numeric|min:0',
            'enable_flash_sale' => 'nullable|string',
            'flash_sale_end_time' => 'nullable|string',
            'flash_sale_title' => 'nullable|string|max:255',
            'trust_badge_1_title' => 'nullable|string|max:100',
            'trust_badge_1_desc' => 'nullable|string|max:150',
            'trust_badge_2_title' => 'nullable|string|max:100',
            'trust_badge_2_desc' => 'nullable|string|max:150',
            'trust_badge_3_title' => 'nullable|string|max:100',
            'trust_badge_3_desc' => 'nullable|string|max:150',
            'trust_badge_4_title' => 'nullable|string|max:100',
            'trust_badge_4_desc' => 'nullable|string|max:150',
            'allow_customer_cancellation' => 'nullable|string',
            'facebook_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'youtube_url' => 'nullable|url|max:255',
            'whatsapp_api_token' => 'nullable|string|max:500',
            'whatsapp_phone_number_id' => 'nullable|string|max:255',
            'whatsapp_template_name' => 'nullable|string|max:255',
        ]);

        foreach ($validated as $key => $value) {
            if ($request->hasFile($key)) {
                // Delete old file if exists
                $oldValue = Setting::get($key);
                if ($oldValue) {
                    Storage::disk('public')->delete($oldValue);
                }
                $value = $request->file($key)->store('settings', 'public');
            }
            Setting::set($key, $value);
        }

        return back()->with('toast', ['type' => 'success', 'title' => 'Success', 'message' => 'Settings updated successfully.']);
    }
}
