<?php

declare(strict_types=1);

use App\Enums\ServiceInterest;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\BusinessSettingController;
use App\Http\Controllers\Admin\JobOperationsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Development\DevelopmentRoadmapController;
use App\Http\Controllers\Development\DevelopmentTaskController;
use App\Http\Controllers\Development\DevelopmentTaskHistoryController;
use App\Http\Controllers\Development\DevelopmentTaskInformationRequestController;
use App\Http\Controllers\Development\DevelopmentTaskLinkController;
use App\Http\Controllers\Development\DevelopmentTaskNoteController;
use App\Http\Controllers\Development\DevelopmentTaskProgressController;
use App\Http\Controllers\Development\DevelopmentTaskPromotionController;
use App\Http\Controllers\Development\DevelopmentTaskRejectionController;
use App\Http\Controllers\Development\DevelopmentTaskReviewController;
use App\Http\Controllers\Development\DevelopmentTaskRoadmapController;
use App\Http\Controllers\Development\DevelopmentTestRunController;
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

    Route::prefix('development')->name('development.')->group(function (): void {
        Route::get('/', [DevelopmentTaskController::class, 'overview'])->name('index');
        Route::get('requests', [DevelopmentTaskController::class, 'index'])->name('requests.index');
        Route::get('requests/create', [DevelopmentTaskController::class, 'create'])->name('requests.create');
        Route::post('requests', [DevelopmentTaskController::class, 'store'])->name('requests.store');
        Route::get('review', [DevelopmentTaskReviewController::class, 'index'])->name('review.index');
        Route::get('tasks/{developmentTask}/history', DevelopmentTaskHistoryController::class)->name('tasks.history');
        Route::get('tasks/{developmentTask}', [DevelopmentTaskController::class, 'show'])->name('tasks.show');
        Route::post('tasks/{developmentTask}/notes', [DevelopmentTaskNoteController::class, 'store'])->name('tasks.notes.store');
        Route::post('tasks/{developmentTask}/review', [DevelopmentTaskReviewController::class, 'store'])->name('tasks.review.store');
        Route::post('tasks/{developmentTask}/information-request', DevelopmentTaskInformationRequestController::class)
            ->name('tasks.informationRequest.store');
        Route::post('tasks/{developmentTask}/promote', DevelopmentTaskPromotionController::class)
            ->name('tasks.promote');
        Route::post('tasks/{developmentTask}/reject', DevelopmentTaskRejectionController::class)
            ->name('tasks.reject');
        Route::patch('tasks/{developmentTask}/roadmap', [DevelopmentTaskRoadmapController::class, 'update'])
            ->name('tasks.roadmap.update');
        Route::patch('tasks/{developmentTask}/progress', [DevelopmentTaskProgressController::class, 'update'])
            ->name('tasks.progress.update');
        Route::post('tasks/{developmentTask}/links', [DevelopmentTaskLinkController::class, 'store'])
            ->name('tasks.links.store');
        Route::post('tasks/{developmentTask}/test-runs', [DevelopmentTestRunController::class, 'store'])
            ->name('tasks.testRuns.store');
        Route::patch('test-runs/{developmentTestRun}', [DevelopmentTestRunController::class, 'update'])
            ->name('testRuns.update');

        Route::get('roadmap', DevelopmentRoadmapController::class)
            ->middleware('can:development.roadmap.manage')
            ->name('roadmap.index');

        Route::get('user-testing', [DevelopmentTestRunController::class, 'index'])->name('userTesting.index');

        Route::middleware('can:development.user_testing.manage')->group(function (): void {
            Route::get('user-testing/legacy-checklist', [UserTestingPageController::class, 'index'])->name('userTesting.legacy');
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
