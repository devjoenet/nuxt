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
        ->assertSee('Operations application');

    $page->page()->getByRole('option', ['name' => 'Operations application', 'exact' => true])->click();
    $page->wait(0.5)->assertDontSee('Use the contact form');

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
        ->assertSee('Your message was sent successfully.')
        ->assertAttribute('[role="status"]', 'aria-live', 'polite')
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

test('budget input stays empty until typed and formats on blur', function (): void {
    visit('/#contact')
        ->assertScript('document.querySelector("#intake-budget")?.value', '')
        ->type('#intake-budget', '25000')
        ->assertScript('document.querySelector("#intake-budget")?.value', '25000')
        ->click('#intake-name')
        ->assertScript('document.querySelector("#intake-budget")?.value', '$25,000')
        ->assertNoJavaScriptErrors();
});

test('landing form masks phone numbers and keeps service options readable', function (): void {
    visit('/#contact')
        ->type('#intake-phone', '1112221234')
        ->assertScript('document.querySelector("#intake-phone")?.value', '111-222-1234')
        ->assertScript('document.querySelector("#intake-service")?.getBoundingClientRect().right > document.querySelector("#intake-service")?.closest(".form-field__shell")?.getBoundingClientRect().right - 8', true)
        ->assertScript('document.querySelector("#intake-timeline")?.getBoundingClientRect().right > document.querySelector("#intake-timeline")?.closest(".form-field__shell")?.getBoundingClientRect().right - 8', true)
        ->click('#intake-service')
        ->assertSee('Reporting and visibility')
        ->assertScript("getComputedStyle(document.querySelector('[role=\"option\"] [data-slot=\"itemLabel\"]')).textOverflow", 'clip')
        ->assertScript("getComputedStyle(document.querySelector('[role=\"option\"] [data-slot=\"itemLabel\"]')).whiteSpace", 'normal')
        ->click('#intake-name')
        ->wait(0.3)
        ->click('#intake-details')
        ->assertScript('document.activeElement?.id', 'intake-details')
        ->assertScript('getComputedStyle(document.querySelector("#intake-details").closest(".form-field__shell")?.querySelector(".form-field__label")).top', '8px')
        ->assertNoJavaScriptErrors();
});
