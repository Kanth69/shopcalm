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

        $rawIdentifier = trim((string) $this->input('login_identifier'));
        $isEmail = filter_var($rawIdentifier, FILTER_VALIDATE_EMAIL);

        if ($isEmail) {
            $field = 'email';
            $identifier = strtolower($rawIdentifier);
            $user = User::where('email', $identifier)->first();
        } else {
            $field = 'mobile_number';
            $cleanDigits = preg_replace('/\D/', '', $rawIdentifier);
            $identifier = (strlen($cleanDigits) >= 10) ? substr($cleanDigits, -10) : $cleanDigits;
            $user = User::where('mobile_number', $identifier)->first();

            // Fallback: if not found by last 10 digits, try exact match as entered
            if (!$user) {
                $user = User::where('mobile_number', $rawIdentifier)->first();
                if ($user) {
                    $identifier = $rawIdentifier;
                }
            }
        }

        $password = (string) $this->input('password');
        $genericErrorMessage = 'Invalid credentials. Please check your mobile/email and password.';

        // Check password: also check trim($password) to gracefully handle mobile keyboard trailing space
        $passwordMatches = false;
        if ($user) {
            if (\Hash::check($password, $user->password)) {
                $passwordMatches = true;
            } elseif (\Hash::check(trim($password), $user->password)) {
                $passwordMatches = true;
                $password = trim($password);
            }
        }

        // 1. Pre-validation for credentials and existence
        if (!$user || !$passwordMatches) {
             RateLimiter::hit($this->throttleKey());
             throw ValidationException::withMessages([
                 'login_identifier' => $genericErrorMessage,
             ]);
        }

        // Exception: Account Blocked or Deleted (Only shown if credentials match)
        if ($user->status === 'Blocked' || $user->status === 'Deleted') {
            throw ValidationException::withMessages([
                'login_identifier' => 'This account has been closed or blocked.',
            ]);
        }

        // 2. Enforce guard-specific role access
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

        // 3. Attempt actual login using the requested guard with normalized credentials
        $credentials = [$field => $identifier, 'password' => $password];

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
