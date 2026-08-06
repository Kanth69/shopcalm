<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProfileSetupController extends Controller
{
    public function show()
    {
        $user = Auth::guard('customer')->user();

        if ($user->profile_prompt_seen) {
            return redirect()->route('dashboard');
        }

        $user->load(['profile', 'interests']);
        $categories = Category::where('status', 'Active')->orderBy('name')->get();

        return view('customer.account.profile-setup', compact('user', 'categories'));
    }

    public function update(Request $request)
    {
        $user = Auth::guard('customer')->user();

        // All fields are optional
        $validated = $request->validate([
            'gender' => ['nullable', 'in:Male,Female,Other,Prefer not to say'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'preferred_language' => ['nullable', 'string', 'max:50'],
            'interests' => ['nullable', 'array'],
            'interests.*' => ['exists:categories,id'],
        ]);

        try {
            DB::transaction(function () use ($user, $validated) {
                // 1. Create/Update Profile
                $user->profile()->updateOrCreate(
                    ['user_id' => $user->id],
                    collect($validated)->only(['gender', 'date_of_birth', 'preferred_language'])->toArray()
                );

                // 2. Update Interests
                if (isset($validated['interests'])) {
                    $user->interests()->sync($validated['interests']);
                }

                // 3. Mark prompt as seen
                $user->profile_prompt_seen = true;
                $user->save();
            });

            return redirect()->route('dashboard')->with('toast', ['type' => 'success', 'title' => 'Welcome!', 'message' => 'Profile updated successfully.']);

        } catch (\Exception $e) {
            return back()->with('toast', ['type' => 'error', 'title' => 'Error', 'message' => 'Failed to save profile.']);
        }
    }

    public function skip()
    {
        $user = Auth::guard('customer')->user();
        $user->profile_prompt_seen = true;
        $user->save();

        return redirect()->route('dashboard');
    }
}
