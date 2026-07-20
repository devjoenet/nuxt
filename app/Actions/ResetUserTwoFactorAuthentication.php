<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class ResetUserTwoFactorAuthentication
{
    public function handle(User $user): void
    {
        if (! $user->hasEnabledTwoFactorAuthentication()) {
            throw ValidationException::withMessages(['two_factor' => 'Two-factor authentication is not configured for this user.']);
        }

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        activity()->performedOn($user)->log('Two-factor authentication reset.');
    }
}
