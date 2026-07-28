<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

test('admin, jobs, settings, and audit screens smoke cleanly', function (): void {
    app(RolePermissionSeeder::class)->run();

    $user = User::factory()->create();
    $user->assignRole('Admin');

    $this->actingAs($user);

    $page = visit('/admin')
        ->assertSee('Admin')
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
        ->assertSee('Audit log')
        ->assertNoJavaScriptErrors()
        ->navigate('/settings/profile')
        ->assertSee('Profile')
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
