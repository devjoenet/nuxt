<?php

declare(strict_types=1);

use App\Enums\ServiceInterest;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\BusinessSettingController;
use App\Http\Controllers\Admin\JobOperationsController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Crm\ContactController;
use App\Http\Controllers\Crm\CrmRecordOptionController;
use App\Http\Controllers\Crm\FollowUpTaskCompletionController;
use App\Http\Controllers\Crm\FollowUpTaskController;
use App\Http\Controllers\Crm\LeadArchiveController;
use App\Http\Controllers\Crm\LeadController;
use App\Http\Controllers\Crm\LeadConversionController;
use App\Http\Controllers\Crm\LeadNoteController;
use App\Http\Controllers\Crm\OrganizationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Development\DevelopmentRoadmapController;
use App\Http\Controllers\Development\DevelopmentTaskController;
use App\Http\Controllers\Development\DevelopmentTaskInformationRequestController;
use App\Http\Controllers\Development\DevelopmentTaskNoteController;
use App\Http\Controllers\Development\DevelopmentTaskPromotionController;
use App\Http\Controllers\Development\DevelopmentTaskRejectionController;
use App\Http\Controllers\Development\DevelopmentTaskReviewController;
use App\Http\Controllers\Development\UserTestingPageController;
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

    Route::prefix('crm')->name('crm.')->middleware('can:crm.view')->group(function (): void {
        Route::get('options/contacts', [CrmRecordOptionController::class, 'contacts'])->name('options.contacts');
        Route::get('options/organizations', [CrmRecordOptionController::class, 'organizations'])->name('options.organizations');
        Route::resource('leads', LeadController::class)->except('destroy');
        Route::resource('organizations', OrganizationController::class)->except('destroy');
        Route::post('organizations/{organization}/archive', [OrganizationController::class, 'archive'])->name('organizations.archive');
        Route::delete('organizations/{organization}/archive', [OrganizationController::class, 'restore'])->name('organizations.restore');
        Route::delete('organizations/{organization}', [OrganizationController::class, 'destroy'])->name('organizations.destroy');
        Route::resource('contacts', ContactController::class)->except('destroy');
        Route::post('contacts/{contact}/archive', [ContactController::class, 'archive'])->name('contacts.archive');
        Route::delete('contacts/{contact}/archive', [ContactController::class, 'restore'])->name('contacts.restore');
        Route::delete('contacts/{contact}', [ContactController::class, 'destroy'])->name('contacts.destroy');
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
        Route::post('users/{user}/reset-two-factor', [AdminUserController::class, 'resetTwoFactor'])
            ->middleware('throttle:two-factor-management')
            ->name('users.resetTwoFactor');

        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
        Route::patch('roles/{role}', [RoleController::class, 'update'])->name('roles.update');

        Route::get('audit', [ActivityLogController::class, 'index'])->name('audit.index');

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

    Route::prefix('development')->name('development.')->group(function (): void {
        Route::get('/', [DevelopmentTaskController::class, 'overview'])->name('index');
        Route::get('requests', [DevelopmentTaskController::class, 'index'])->name('requests.index');
        Route::get('requests/create', [DevelopmentTaskController::class, 'create'])->name('requests.create');
        Route::post('requests', [DevelopmentTaskController::class, 'store'])->name('requests.store');
        Route::get('review', [DevelopmentTaskReviewController::class, 'index'])->name('review.index');
        Route::get('tasks/{developmentTask}', [DevelopmentTaskController::class, 'show'])->name('tasks.show');
        Route::post('tasks/{developmentTask}/notes', [DevelopmentTaskNoteController::class, 'store'])->name('tasks.notes.store');
        Route::post('tasks/{developmentTask}/review', [DevelopmentTaskReviewController::class, 'store'])->name('tasks.review.store');
        Route::post('tasks/{developmentTask}/information-request', DevelopmentTaskInformationRequestController::class)
            ->name('tasks.informationRequest.store');
        Route::post('tasks/{developmentTask}/promote', DevelopmentTaskPromotionController::class)
            ->name('tasks.promote');
        Route::post('tasks/{developmentTask}/reject', DevelopmentTaskRejectionController::class)
            ->name('tasks.reject');

        Route::get('roadmap', DevelopmentRoadmapController::class)
            ->middleware('can:development.roadmap.manage')
            ->name('roadmap.index');

        Route::middleware('can:development.user_testing.manage')->group(function (): void {
            Route::get('user-testing', [UserTestingPageController::class, 'index'])->name('userTesting.index');
            Route::get('user-testing/updates', [UserTestingPageController::class, 'updates'])->name('userTesting.updates');
            Route::patch('user-testing/{userTestingPage}', [UserTestingPageController::class, 'update'])->name('userTesting.update');
        });
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

foreach (Route::getRoutes()->getRoutes() as $route) {
    if (in_array($route->getName(), ['two-factor.enable', 'two-factor.confirm', 'two-factor.disable', 'two-factor.regenerate-recovery-codes'], true)) {
        $route->middleware('throttle:two-factor-management');
    }
}
