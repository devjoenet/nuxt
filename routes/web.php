<?php

declare(strict_types=1);

use App\Http\Controllers\Crm\FollowUpTaskCompletionController;
use App\Http\Controllers\Crm\FollowUpTaskController;
use App\Http\Controllers\Crm\LeadArchiveController;
use App\Http\Controllers\Crm\LeadController;
use App\Http\Controllers\Crm\LeadConversionController;
use App\Http\Controllers\Crm\LeadNoteController;
use App\Http\Controllers\PublicIntakeController;
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

Route::middleware(['auth', 'verified'])->group(function (): void {
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
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
