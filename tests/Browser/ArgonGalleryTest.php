<?php

declare(strict_types=1);

use App\Enums\Permission as PermissionName;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Permission\Models\Role;

test('authorized users can filter the manifest and inspect theme viewport and lazy lab states', function (): void {
    app(RolePermissionSeeder::class)->run();

    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $this->actingAs($admin);

    visit(route('admin.argonGallery.index', absolute: false))
        ->assertSee('69 of 69 entries shown.')
        ->fill('input[name="gallery-search"]', 'account-billing')
        ->assertSee('1 of 69 entries shown.')
        ->assertSee('Billing')
        ->assertDontSee('Invoice')
        ->click('[data-testid="gallery-theme-dark"]')
        ->assertScript('document.documentElement.classList.contains("dark")', true)
        ->click('[data-testid="gallery-viewport-mobile"]')
        ->assertScript('getComputedStyle(document.querySelector("[data-testid=gallery-preview]")).maxWidth', '390px')
        ->click('Advanced')
        ->assertSee('Advanced adapters')
        ->resize(390, 844)
        ->assertScript('document.documentElement.scrollWidth <= window.innerWidth', true)
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs()
        ->assertNoAccessibilityIssues();
});

test('gallery-only users receive a usable permission-filtered navigation destination', function (): void {
    app(RolePermissionSeeder::class)->run();

    $role = Role::findOrCreate('Gallery Reviewer', 'web');
    $role->givePermissionTo(PermissionName::ArgonGalleryView->value);
    $user = User::factory()->create();
    $user->assignRole($role);

    $this->actingAs($user);

    visit(route('dashboard', absolute: false))
        ->assertPresent('a[href="/admin/argon-gallery"]')
        ->assertMissing('a[href="/admin"]')
        ->click('Argon gallery')
        ->assertPathIs(route('admin.argonGallery.index', absolute: false))
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});
