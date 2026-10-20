<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Facades\Activity;

class AcceptUserInvitation
{
    public function handle(User $actor): void
    {
        DB::transaction(function () use ($actor): void {
            $user = User::query()->whereKey($actor)->lockForUpdate()->firstOrFail();
            if ($user->invited_at === null || $user->invitation_accepted_at !== null) {
                return;
            }

            Activity::defaultCauser($actor, fn (): bool => $user->update(['invitation_accepted_at' => now()]));
        });
    }
}
