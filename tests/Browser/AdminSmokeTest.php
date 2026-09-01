<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

test('the workspace shell preserves CRM, admin, settings, and Development screens', function (): void {
    app(RolePermissionSeeder::class)->run();

    $user = User::factory()->create();
    $user->assignRole('Admin');

    $this->actingAs($user);

    $page = visit('/admin')
        ->assertTitle('Admin dashboard - Southeast Code')
        ->assertCount('main[data-workspace-page-content]', 1)
        ->assertDataAttribute('main[data-workspace-page-content]', 'content-width', 'full')
        ->assertSee('Admin')
        ->assertSee('Development')
        ->assertSee('Recent activity')
        ->assertSee('Southeast Code')
        ->assertDontSee('Create team')
        ->assertDontSee('Manage teams')
        ->assertNoJavaScriptErrors()
        ->navigate('/admin/users')
        ->assertSee('Users')
        ->assertSee('Invite a user')
        ->assertNoJavaScriptErrors()
        ->navigate('/admin/roles')
        ->assertSee('Roles and permissions')
        ->assertNoJavaScriptErrors()
        ->navigate('/admin/jobs')
        ->assertSee('Jobs & queues')
        ->assertNoJavaScriptErrors()
        ->navigate('/admin/settings')
        ->assertSee('Business settings')
        ->assertNoJavaScriptErrors()
        ->navigate('/admin/audit')
        ->assertTitle('Audit log - Southeast Code')
        ->assertSee('Audit log')
        ->assertPresent('.audit-toolbar')
        ->assertNoJavaScriptErrors()
        ->navigate('/crm/organizations')
        ->assertTitle('Organizations - Southeast Code')
        ->assertCount('main[data-workspace-page-content]', 1)
        ->assertSee('Organizations')
        ->assertSee('New organization')
        ->assertNoJavaScriptErrors()
        ->navigate('/development/roadmap')
        ->assertTitle('Development roadmap - Southeast Code')
        ->assertSee('Development roadmap')
        ->assertCount('main[data-workspace-page-content]', 1)
        ->assertNoJavaScriptErrors()
        ->navigate('/development/user-testing')
        ->assertSee('User testing')
        ->assertNoJavaScriptErrors()
        ->navigate('/settings/profile')
        ->assertTitle('Profile settings - Southeast Code')
        ->assertSee('Profile')
        ->assertDataAttribute('main[data-workspace-page-content]', 'content-width', 'narrow')
        ->resize(390, 844)
        ->assertScript('document.documentElement.scrollWidth <= window.innerWidth', true)
        ->assertNoJavaScriptErrors()
        ->navigate('/settings/security')
        ->assertSee('Confirm your password')
        ->fill('password', 'password')
        ->click('Confirm Password')
        ->assertPathIs('/settings/security')
        ->assertSee('Password')
        ->assertNoJavaScriptErrors();

    $page->assertPathIs('/settings/security');
});

test('the auth shell and floating labels use the specified light and dark surfaces', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user);

    $lightPage = visit('/user/confirm-password')->inLightMode();
    $lightPage
        ->assertScript('getComputedStyle(document.querySelector("[data-testid=auth-shell-card]")).backgroundColor', 'rgb(255, 255, 255)')
        ->assertScript('getComputedStyle(document.querySelector("[data-testid=auth-shell-card] .form-field__shell[data-variant=outlined]")).backgroundColor', 'rgba(0, 0, 0, 0)')
        ->assertScript('getComputedStyle(document.querySelector("[data-testid=auth-shell-card] .form-field__label")).backgroundColor', 'rgb(255, 255, 255)');

    $lightPage->page()->locator('input[name="password"]')->press('Tab');

    $lightPage
        ->wait(0.2)
        ->assertScript('getComputedStyle(document.querySelector("[data-testid=auth-shell-card] .form-field__shell[data-variant=outlined]")).borderColor === getComputedStyle(document.querySelector("[data-testid=auth-shell-card] .form-field__label")).color', true)
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();

    $darkPage = visit('/user/confirm-password')->inDarkMode();
    $darkPage
        ->assertScript('getComputedStyle(document.querySelector("[data-testid=auth-shell-card]")).backgroundColor', 'rgb(26, 32, 53)')
        ->assertScript('getComputedStyle(document.querySelector("[data-testid=auth-shell-card] .form-field__shell[data-variant=outlined]")).backgroundColor', 'rgba(0, 0, 0, 0)')
        ->assertScript('getComputedStyle(document.querySelector("[data-testid=auth-shell-card] .form-field__label")).backgroundColor', 'rgb(26, 32, 53)');

    $darkPage->page()->locator('input[name="password"]')->press('Tab');

    $darkPage
        ->wait(0.2)
        ->assertScript('getComputedStyle(document.querySelector("[data-testid=auth-shell-card] .form-field__shell[data-variant=outlined]")).borderColor === getComputedStyle(document.querySelector("[data-testid=auth-shell-card] .form-field__label")).color', true)
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});

test('admin user controls only show two-factor reset for configured users', function (): void {
    app(RolePermissionSeeder::class)->run();

    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $twoFactorUser = User::factory()->withTwoFactorAuthentication()->create();
    $unverified = User::factory()->unverified()->create();

    $this->actingAs($admin);

    visit('/admin/users')
        ->assertSee('Resend verification')
        ->assertSee('Reset 2FA')
        ->assertPresent("[data-testid=\"reset-2fa-{$twoFactorUser->getKey()}\"]")
        ->assertMissing("[data-testid=\"reset-2fa-{$unverified->getKey()}\"]")
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});

test('two-factor setup and recovery steps keep their state inside the security page', function (): void {
    $user = User::factory()->withoutTwoFactor()->create();

    $this->actingAs($user);

    $page = visit('/settings/security')
        ->fill('password', 'password')
        ->click('Confirm Password')
        ->assertPathIs('/settings/security')
        ->click('Enable 2FA')
        ->wait(0.8)
        ->assertSee('Enable Two-Factor Authentication')
        ->assertSee('or, enter the code manually')
        ->assertPresent('[role="dialog"]')
        ->click('Continue')
        ->assertSee('Verify Authentication Code')
        ->click('Back')
        ->assertSee('or, enter the code manually')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();

    expect($user->refresh()->two_factor_secret)->not->toBeNull();
});

test('two factor recovery codes regenerate inside the security page', function (): void {
    $configuredUser = User::factory()->withTwoFactorAuthentication()->create();
    $originalRecoveryCodes = $configuredUser->two_factor_recovery_codes;

    $this->actingAs($configuredUser);

    visit('/settings/security')
        ->fill('password', 'password')
        ->click('Confirm Password')
        ->assertPathIs('/settings/security')
        ->assertSee('Enabled')
        ->click('View Recovery Codes')
        ->wait(0.5)
        ->assertSee('Regenerate Codes')
        ->click('Regenerate Codes')
        ->wait(0.5)
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();

    expect($configuredUser->refresh()->two_factor_recovery_codes)
        ->not->toBe($originalRecoveryCodes);
});

test('client viewers do not see CRM navigation', function (): void {
    app(RolePermissionSeeder::class)->run();

    $user = User::factory()->create();
    $user->assignRole('Client Viewer');

    $this->actingAs($user);

    visit('/dashboard')
        ->assertSee('Workspace')
        ->assertMissing('a[href="/crm/leads"]')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});

test('development sidebar only marks the current request page as active', function (): void {
    app(RolePermissionSeeder::class)->run();

    $user = User::factory()->create();
    $user->assignRole('Admin');

    $this->actingAs($user);

    $requestsPath = route('development.requests.index', absolute: false);
    $submitRequestPath = route('development.requests.create', absolute: false);

    visit($submitRequestPath)
        ->assertAriaAttribute("a[data-slot=\"link\"][href=\"{$submitRequestPath}\"]", 'current', 'page')
        ->assertAttributeMissing("a[data-slot=\"link\"][href=\"{$requestsPath}\"]", 'aria-current')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});
