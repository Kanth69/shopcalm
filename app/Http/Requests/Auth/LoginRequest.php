<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'login_identifier' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(string $guard): void
    {
        $this->ensureIsNotRateLimited();

        $identifier = $this->input('login_identifier');
        $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile_number';
        $credentials = [$field => $identifier, 'password' => $this->input('password')];

        $genericErrorMessage = 'Invalid password.';

        // 1. Pre-validation for role and status
        $user = User::where($field, $identifier)->first();

        // Standardize failure messages to be generic (Security standard)
        if (!$user || !\Hash::check($this->input('password'), $user->password)) {
             RateLimiter::hit($this->throttleKey());
             throw ValidationException::withMessages([
                 'login_identifier' => $genericErrorMessage,
             ]);
        }

        // Exception: Account Blocked (Only shown if credentials are correct)
        if ($user->status === 'Blocked') {
            throw ValidationException::withMessages([
                'login_identifier' => 'Your account has been blocked. Please contact support.',
            ]);
        }

        // 2. Enforce guard-specific role access using generic message
        if ($guard === 'admin') {
            if (!$user->isAdmin()) {
                throw ValidationException::withMessages([
                    'login_identifier' => $genericErrorMessage,
                ]);
            }
        } else {
            if (!$user->isCustomer()) {
                throw ValidationException::withMessages([
                    'login_identifier' => $genericErrorMessage,
                ]);
            }
        }

        // 3. Attempt actual login using the requested guard
        if (! Auth::guard($guard)->attempt($credentials, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'login_identifier' => $genericErrorMessage,
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login_identifier' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('login_identifier')).'|'.$this->ip());
    }
}
