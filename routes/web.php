<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\BusinessSettingController;
use App\Http\Controllers\Admin\JobOperationsController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Crm\FollowUpTaskCompletionController;
use App\Http\Controllers\Crm\FollowUpTaskController;
use App\Http\Controllers\Crm\LeadArchiveController;
use App\Http\Controllers\Crm\LeadController;
use App\Http\Controllers\Crm\LeadConversionController;
use App\Http\Controllers\Crm\LeadNoteController;
use App\Http\Controllers\PublicIntakeController;
use App\Http\Middleware\EnsureUserIsActive;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Welcome', [
    'canonicalUrl' => config('app.url'),
]))->name('home');
Route::get('privacy', fn () => Inertia::render('Privacy', [
    'canonicalUrl' => config('app.url').'/privacy',
]))->name('privacy');
Route::post('contact', PublicIntakeController::class)
    ->middleware('throttle:public-intake')
    ->name('contact.store');

Route::middleware(['auth', EnsureUserIsActive::class, 'verified'])->group(function (): void {
    Route::get('dashboard', fn () => Inertia::render('Dashboard'))->name('dashboard');
    Route::get('inbox', fn () => Inertia::render('Inbox'))->name('inbox');

    Route::prefix('crm')->name('crm.')->middleware('can:crm.view')->group(function (): void {
        Route::resource('leads', LeadController::class)->except('destroy');
        Route::post('leads/{lead}/archive', [LeadArchiveController::class, 'store'])->name('leads.archive');
        Route::delete('leads/{lead}/archive', [LeadArchiveController::class, 'destroy'])->name('leads.restore');
        Route::post('leads/{lead}/convert', LeadConversionController::class)->name('leads.convert');
        Route::post('leads/{lead}/notes', [LeadNoteController::class, 'store'])->name('leads.notes.store');
        Route::post('leads/{lead}/tasks', [FollowUpTaskController::class, 'store'])->name('leads.tasks.store');
        Route::post('leads/{lead}/tasks/{task}/complete', FollowUpTaskCompletionController::class)->name('leads.tasks.complete');
    });

    Route::prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/', AdminDashboardController::class)->name('dashboard');

        Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
        Route::post('users', [AdminUserController::class, 'store'])->name('users.store');
        Route::patch('users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::post('users/{user}/resend-verification', [AdminUserController::class, 'resendVerification'])->name('users.resendVerification');
        Route::post('users/{user}/reset-two-factor', [AdminUserController::class, 'resetTwoFactor'])->name('users.resetTwoFactor');

        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
        Route::patch('roles/{role}', [RoleController::class, 'update'])->name('roles.update');

        Route::get('audit', [ActivityLogController::class, 'index'])->name('audit.index');

        Route::get('settings', [BusinessSettingController::class, 'edit'])->name('settings.edit');
        Route::patch('settings', [BusinessSettingController::class, 'update'])->name('settings.update');

        Route::get('jobs', [JobOperationsController::class, 'index'])
            ->middleware('can:jobs.view')
            ->name('jobs.index');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
