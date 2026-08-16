<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $user = User::where('email', $googleUser->email)->orWhere('google_id', $googleUser->id)->first();

            if ($user) {
                // Strictly block login if account is blocked
                if ($user->status === 'Blocked') {
                    return redirect()->route('login')->withErrors([
                        'login_identifier' => 'Your account has been blocked. Please contact support.',
                    ]);
                }

                if (!$user->google_id) {
                    $user->update([
                        'google_id' => $googleUser->id,
                        'avatar' => $googleUser->avatar
                    ]);
                }
                Auth::guard('customer')->login($user);
            } else {
                $newUser = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'password' => Hash::make(Str::random(16)),
                    'role_id' => User::ROLE_CUSTOMER,
                    'status' => 'Active',
                    'avatar' => $googleUser->avatar
                ]);

                Auth::guard('customer')->login($newUser);
            }

            return redirect()->intended(route('home', absolute: false));

        } catch (Exception $e) {
            return redirect()->route('login')->with('error', 'Something went wrong with Google Login. Please try again.');
        }
    }
}
