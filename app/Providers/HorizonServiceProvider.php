<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;
use Override;

use function in_array;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    #[Override]
    public function boot(): void
    {
        parent::boot();

        // Horizon::routeSmsNotificationsTo('15556667777');
        // Horizon::routeMailNotificationsTo('example@example.com');
        // Horizon::routeSlackNotificationsTo('slack-webhook-url', '#channel');
    }

    #[Override]
    protected function gate(): void
    {
        Gate::define('viewHorizon', function (?User $user = null): bool {
            if (! $user instanceof User) {
                return false;
            }

            /** @var array<string> $allowedEmails */
            $allowedEmails = [
                //
            ];

            return in_array($user->email, $allowedEmails, true);
        });
    }
}
