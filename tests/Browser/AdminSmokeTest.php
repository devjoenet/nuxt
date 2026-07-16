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
