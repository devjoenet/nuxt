<?php

declare(strict_types=1);

use App\Models\User;

test('public marketing and intake smoke cleanly in a dark browser session', function (): void {
    $page = visit('/#contact')
        ->inDarkMode()
        ->assertSee('Let’s map the workflow before anyone writes code.')
        ->assertSee('Send project details')
        ->assertNoJavaScriptErrors()
        ->assertScript('document.documentElement.classList.contains("dark")', true);

    $page
        ->click('#intake-service')
        ->assertAttribute('#intake-service', 'aria-expanded', 'true')
        ->assertSee('Operations application')
        ->page()
        ->getByRole('option', ['name' => 'Operations application', 'exact' => true])
        ->click();

    $page
        ->click('#intake-name')
        ->keys('#intake-name', 'Tab')
        ->assertScript('document.activeElement?.id', 'intake-email')
        ->fill('#intake-name', 'Jordan Rivera')
        ->fill('#intake-email', 'not-an-email')
        ->fill('#intake-details', 'Too short')
        ->check('#intake-consent')
        ->click('Send project details')
        ->assertScript('document.querySelector("#intake-email")?.validity.typeMismatch', true)
        ->assertScript('document.activeElement?.id', 'intake-email')
        ->assertNoJavaScriptErrors();
});

test('visitors can submit a valid inquiry from the browser', function (): void {
    visit('/#contact')
        ->fill('#intake-name', 'Jordan Rivera')
        ->fill('#intake-email', 'jordan@example.com')
        ->fill('#intake-company', 'Rivera Logistics')
        ->fill('#intake-details', 'Our dispatch workflow depends on several spreadsheets and repeated manual entry.')
        ->check('#intake-consent')
        ->click('Send project details')
        ->assertSee('Thanks. Your inquiry is in.')
        ->assertNoJavaScriptErrors();
});

test('users can sign in through the browser and reach the workspace', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();

    visit('/login')
        ->assertSee('Log in to your account')
        ->fill('email', $user->email)
        ->fill('password', 'password')
        ->click('Log in')
        ->assertPathIs('/dashboard')
        ->assertSee('Workspace')
        ->assertSee('Southeast Code')
        ->assertDontSee('Create team')
        ->assertDontSee('Manage teams')
        ->assertNoJavaScriptErrors();
});

test('filled login inputs float their labels', function (): void {
    visit('/login')
        ->fill('email', 'test@example.com')
        ->fill('password', 'password')
        ->wait(0.2)
        ->assertScript('document.querySelector(\'.form-field__shell:has(input[name="password"]) .form-field__label\')?.getBoundingClientRect().top < document.querySelector(\'.form-field__shell:has(input[name="password"])\')?.getBoundingClientRect().top + 8', true)
        ->assertNoJavaScriptErrors();
});
