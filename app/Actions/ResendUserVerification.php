<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class ResendUserVerification
{
    public function handle(User $user): void
    {
        if ($user->hasVerifiedEmail()) {
            throw ValidationException::withMessages(['email' => 'This user has already verified their email address.']);
        }

        $user->sendEmailVerificationNotification();

        activity()->performedOn($user)->log('Verification email resent.');
    }
}
