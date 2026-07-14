<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\BusinessSetting;
use App\Models\User;
use App\Policies\ActivityPolicy;
use App\Policies\BusinessSettingPolicy;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Override;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('public-intake', fn (Request $request): Limit => Limit::perMinute(5)->by($request->ip()));

        Gate::before(fn (?User $user): ?bool => $user?->hasRole('Super Admin') ? true : null);

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Activity::class, ActivityPolicy::class);
        Gate::policy(BusinessSetting::class, BusinessSettingPolicy::class);
    }
}
