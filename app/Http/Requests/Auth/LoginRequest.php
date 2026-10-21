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
    public function authenticate(string $guard = 'customer'): void
    {
        $this->ensureIsNotRateLimited();

        $rawIdentifier = trim((string) $this->input('login_identifier'));
        $isEmail = (bool) filter_var($rawIdentifier, FILTER_VALIDATE_EMAIL);

        $cleanDigits = preg_replace('/\D/', '', $rawIdentifier);
        $last10 = (strlen($cleanDigits) >= 10) ? substr($cleanDigits, -10) : $cleanDigits;

        // Query candidate users matching the identifier across email and all mobile formats (+91, spaces, 10-digits)
        $candidates = User::where(function ($query) use ($rawIdentifier, $isEmail, $cleanDigits, $last10) {
            if ($isEmail) {
                $query->where('email', strtolower($rawIdentifier));
            } else {
                if (!empty($last10)) {
                    $query->where('mobile_number', 'LIKE', '%' . $last10);
                }
                if (!empty($cleanDigits) && $cleanDigits !== $last10) {
                    $query->orWhere('mobile_number', $cleanDigits);
                }
                $query->orWhere('mobile_number', $rawIdentifier)
                      ->orWhere('email', strtolower($rawIdentifier));
            }
        })->get();

        // Fallback: if candidates empty, check both email and phone without strict filter
        if ($candidates->isEmpty()) {
            $candidates = User::where('email', strtolower($rawIdentifier))
                ->orWhere('mobile_number', $rawIdentifier)
                ->when(!empty($last10), fn($q) => $q->orWhere('mobile_number', 'LIKE', '%' . $last10))
                ->when(!empty($cleanDigits), fn($q) => $q->orWhere('mobile_number', $cleanDigits))
                ->get();
        }

        $password = (string) $this->input('password');
        $genericErrorMessage = 'Invalid credentials. Please check your mobile/email and password.';

        // Check password across candidate accounts, handling mobile auto-space / trailing space gracefully
        $passwordMatches = false;
        $matchedUser = null;

        foreach ($candidates as $candidate) {
            if (\Hash::check($password, $candidate->password)) {
                $passwordMatches = true;
                $matchedUser = $candidate;
                break;
            } elseif (\Hash::check(trim($password), $candidate->password)) {
                $passwordMatches = true;
                $matchedUser = $candidate;
                $password = trim($password);
                break;
            }
        }

        $user = $matchedUser ?: $candidates->first();

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
        } elseif ($guard === 'customer') {
            if (!$user->isCustomer()) {
                throw ValidationException::withMessages([
                    'login_identifier' => $genericErrorMessage,
                ]);
            }
        }

        // 3. Log the user into the session directly (credentials and role fully verified)
        Auth::guard($guard)->login($user, $this->boolean('remember'));

        RateLimiter::clear($this->throttleKey());
        if ($user->email) {
            RateLimiter::clear(Str::transliterate(Str::lower($user->email) . '|' . $this->ip()));
        }
        if ($user->mobile_number) {
            RateLimiter::clear(Str::transliterate(Str::lower($user->mobile_number) . '|' . $this->ip()));
        }
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
