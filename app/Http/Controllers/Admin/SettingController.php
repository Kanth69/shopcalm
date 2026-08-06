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
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $settings = Setting::all()->pluck('value', 'key')->toArray();
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'store_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'currency' => 'nullable|string|max:10',
            'currency_symbol' => 'nullable|string|max:5',
            'logo' => 'nullable|image|max:2048',
            'favicon' => 'nullable|image|max:1024',
            'enable_trust_badges' => 'nullable|string',
            'free_shipping_min' => 'nullable|numeric',
            'enable_flash_sale' => 'nullable|string',
            'flash_sale_end_time' => 'nullable|string',
            'flash_sale_title' => 'nullable|string|max:255',
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
