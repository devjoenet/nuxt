<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function (): void {
    Route::middleware('web')->get('/__task-16/browser-errors/{status}', function (int $status): Response {
        return Inertia::render('ErrorPage', ['status' => $status])
            ->toResponse(request())
            ->setStatusCode($status);
    })->whereNumber('status');
});

dataset('browser error statuses', [403, 404, 419, 429, 500, 503]);

test('supported error pages are branded and usable in dark mode', function (int $status): void {
    visit('/__task-16/browser-errors/'.$status)
        ->inDarkMode()
        ->assertSee((string) $status)
        ->assertSee('Southeast Code')
        ->assertSee('Southeast Code homepage')
        ->assertScript('document.documentElement.classList.contains("dark")', true)
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs()
        ->assertNoAccessibilityIssues();
})->with('browser error statuses');

test('the light desktop error page keeps branding and keyboard actions visible', function (): void {
    visit('/__task-16/browser-errors/403')
        ->assertSee('Southeast Code homepage')
        ->assertScript('document.querySelector("a[aria-label=\"Southeast Code home\"]")?.getBoundingClientRect().width > 0', true)
        ->assertNoJavaScriptErrors()
        ->assertNoAccessibilityIssues();
});

test('the longest error copy fits a narrow viewport without horizontal overflow', function (): void {
    visit('/__task-16/browser-errors/503')
        ->resize(320, 568)
        ->assertSee('Southeast Code is temporarily unavailable')
        ->assertScript('document.documentElement.scrollWidth <= window.innerWidth', true)
        ->assertScript('document.querySelector("a[aria-label=\"Southeast Code home\"]")?.getBoundingClientRect().right <= window.innerWidth', true)
        ->assertScript('Array.from(document.querySelectorAll("a")).every((link) => link.getBoundingClientRect().right <= window.innerWidth)', true)
        ->assertNoJavaScriptErrors()
        ->assertNoAccessibilityIssues();
});
