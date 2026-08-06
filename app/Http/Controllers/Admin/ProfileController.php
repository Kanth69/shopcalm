<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function index()
    {
        $admin = auth()->guard('admin')->user();
        return view('admin.profile.index', compact('admin'));
    }

    public function update(Request $request)
    {
        $admin = auth()->guard('admin')->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $admin->id],
            'mobile_number' => ['required', 'string', 'max:15', 'unique:users,mobile_number,' . $admin->id],
        ]);

        $admin->update($validated);

        return back()->with('toast', ['type' => 'success', 'title' => 'Success', 'message' => 'Profile updated successfully.']);
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        auth()->guard('admin')->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('toast', ['type' => 'success', 'title' => 'Success', 'message' => 'Password changed successfully.']);
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'max:2048'], // 2MB Max
        ]);

        $admin = auth()->guard('admin')->user();

        if ($admin->avatar) {
            Storage::disk('public')->delete($admin->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');

        $admin->update(['avatar' => $path]);

        return back()->with('toast', ['type' => 'success', 'title' => 'Success', 'message' => 'Profile picture updated successfully.']);
    }
}
