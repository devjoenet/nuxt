<?php

declare(strict_types=1);

use App\Enums\ServiceInterest;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\BusinessSettingController;
use App\Http\Controllers\Admin\JobOperationsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PublicIntakeController;
use App\Http\Middleware\EnsureUserIsActive;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Welcome', [
    'canonicalUrl' => config('app.url'),
    'serviceInterestOptions' => ServiceInterest::options(),
]))->name('home');
Route::get('privacy', fn () => Inertia::render('Privacy', [
    'canonicalUrl' => config('app.url').'/privacy',
]))->name('privacy');
Route::post('contact', PublicIntakeController::class)
    ->middleware('throttle:public-intake')
    ->name('contact.store');

Route::middleware(['auth', EnsureUserIsActive::class, 'verified'])->group(function (): void {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/', AdminDashboardController::class)->name('dashboard');

        Route::get('settings', [BusinessSettingController::class, 'edit'])->name('settings.edit');
        Route::patch('settings', [BusinessSettingController::class, 'update'])->name('settings.update');

        Route::get('jobs', [JobOperationsController::class, 'index'])
            ->middleware('can:jobs.view')
            ->name('jobs.index');

        Route::redirect('roadmap', '/development/roadmap')
            ->middleware('can:development.roadmap.manage')
            ->name('roadmap.index');
        Route::redirect('user-testing', '/development/user-testing')
            ->middleware('can:development.user_testing.manage')
            ->name('userTesting.index');
    });

});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

foreach (Route::getRoutes()->getRoutes() as $route) {
    if (in_array($route->getName(), ['two-factor.enable', 'two-factor.confirm', 'two-factor.disable', 'two-factor.regenerate-recovery-codes'], true)) {
        $route->middleware('throttle:two-factor-management');
    }
}
