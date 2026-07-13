<?php

declare(strict_types=1);

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
    Route::get('customers', fn () => Inertia::render('Customers'))->name('customers');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
