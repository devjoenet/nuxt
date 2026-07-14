<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;
use Override;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    #[Override]
    public function boot(): void
    {
        parent::boot();

        $notificationEmail = config('services.horizon.notification_email');

        if (is_string($notificationEmail) && $notificationEmail !== '') {
            Horizon::routeMailNotificationsTo($notificationEmail);
        }
    }

    #[Override]
    protected function gate(): void
    {
        Gate::define('viewHorizon', fn (?User $user = null): bool => $user?->canAny([
            Permission::JobsView->value,
            Permission::JobsManage->value,
        ]) ?? false);
    }
}
