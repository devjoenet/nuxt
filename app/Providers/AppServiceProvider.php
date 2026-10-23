<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\BusinessSetting;
use App\Models\User;
use App\Policies\BusinessSettingPolicy;
use App\Support\AgencyLegacyPageUrlResolver;
use App\Support\AgencyReminderRecipient;
use App\Support\WorkspaceUsers;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Inertia\ExceptionResponse;
use Inertia\Inertia;
use Override;
use SoutheastCode\Crm\FollowUps\Contracts\ReminderRecipient;
use SoutheastCode\DevelopmentWorkflow\Contracts\LegacyPageUrlResolver;

class AppServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->bind(ReminderRecipient::class, AgencyReminderRecipient::class);
        $this->app->bind(LegacyPageUrlResolver::class, AgencyLegacyPageUrlResolver::class);
    }

    public function boot(): void
    {
        Inertia::disableSsr(fn (Request $request): bool => ! $request->routeIs('home', 'privacy'));

        Inertia::handleExceptionsUsing(function (ExceptionResponse $response): ?ExceptionResponse {
            if (! in_array($response->statusCode(), [403, 404, 419, 429, 500, 503], true)) {
                return null;
            }

            return $response
                ->render('ErrorPage', ['status' => $response->statusCode()])
                ->withSharedData();
        });

        RateLimiter::for('public-intake', fn (Request $request): Limit => Limit::perMinute(5)->by($request->ip()));
        RateLimiter::for('login-route', fn (Request $request): Limit => Limit::perMinute(30)->by($request->ip()));
        RateLimiter::for('two-factor-management', fn (Request $request): Limit => Limit::perMinute(10)->by(
            ($request->user()?->getAuthIdentifier() ?? 'guest').'|'.$request->ip(),
        ));

        Gate::before(fn (?User $user): ?bool => $user?->hasRole('Super Admin') ? true : null);

        Route::bind('user', function (string $value): User {
            $user = (new (WorkspaceUsers::model()))->resolveRouteBinding($value);

            return $user instanceof User ? $user : abort(404);
        });
        Gate::policy(BusinessSetting::class, BusinessSettingPolicy::class);
    }
}
