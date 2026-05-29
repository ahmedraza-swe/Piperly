<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class LoginService
{
    public function attempt(array $credentials, bool $remember): bool
    {
        return Auth::guard()->attempt($credentials, $remember);
    }

    public function authenticateUser(User $user, bool $isEmailVerified = false): void
    {
        auth()->login($user);

        if ($isEmailVerified && ! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }
    }
}
