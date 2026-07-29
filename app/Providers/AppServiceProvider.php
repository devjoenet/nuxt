<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\BusinessSetting;
use App\Models\Contact;
use App\Models\DevelopmentTask;
use App\Models\DevelopmentTaskLink;
use App\Models\DevelopmentTaskNote;
use App\Models\DevelopmentTestRun;
use App\Models\Lead;
use App\Models\Organization;
use App\Models\User;
use App\Policies\ActivityPolicy;
use App\Policies\BusinessSettingPolicy;
use App\Policies\ContactPolicy;
use App\Policies\DevelopmentTaskLinkPolicy;
use App\Policies\DevelopmentTaskNotePolicy;
use App\Policies\DevelopmentTaskPolicy;
use App\Policies\DevelopmentTestRunPolicy;
use App\Policies\LeadPolicy;
use App\Policies\OrganizationPolicy;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Inertia\ExceptionResponse;
use Inertia\Inertia;
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

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Activity::class, ActivityPolicy::class);
        Gate::policy(BusinessSetting::class, BusinessSettingPolicy::class);
        Gate::policy(Contact::class, ContactPolicy::class);
        Gate::policy(DevelopmentTask::class, DevelopmentTaskPolicy::class);
        Gate::policy(DevelopmentTaskNote::class, DevelopmentTaskNotePolicy::class);
        Gate::policy(DevelopmentTaskLink::class, DevelopmentTaskLinkPolicy::class);
        Gate::policy(DevelopmentTestRun::class, DevelopmentTestRunPolicy::class);
        Gate::policy(Lead::class, LeadPolicy::class);
        Gate::policy(Organization::class, OrganizationPolicy::class);
    }
}
