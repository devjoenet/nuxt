<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Laravel\Fortify\Fortify;
use Override;

class FortifyServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Fortify::twoFactorChallengeView(fn () => Inertia::render('auth/TwoFactorChallenge'));
        Fortify::confirmPasswordView(fn () => Inertia::render('auth/ConfirmPassword'));

        RateLimiter::for('login', fn (Request $request): Limit => Limit::perMinute(5)->by(
            Str::lower((string) $request->input('email')).'|'.$request->ip(),
        ));
        RateLimiter::for('two-factor', fn (Request $request) => Limit::perMinute(5)->by($request->session()->get('login.id')));
    }
}
