<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $user->load(['profile', 'interests']);
        $categories = Category::where('status', 'Active')->orderBy('name')->get();

        return view('customer.account.profile', [
            'user' => $user,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->only(['name', 'email', 'mobile_number']));

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        // Update Customer Profile metadata
        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            $request->only(['gender', 'date_of_birth', 'preferred_language'])
        );

        // Update Interests
        $user->interests()->sync($request->input('interests', []));

        return Redirect::route('profile.edit')->with('toast', ['type' => 'success', 'title' => 'Success', 'message' => 'Profile updated successfully.']);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
