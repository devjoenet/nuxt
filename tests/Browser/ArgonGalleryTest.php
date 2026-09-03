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

test('the charts reference renders accessibly without mobile overflow', function (): void {
    app(RolePermissionSeeder::class)->run();

    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $this->actingAs($admin);

    visit(route('admin.argonGallery.examples.show', 'general-charts', absolute: false))
        ->assertSee('Revenue trend')
        ->assertSee('Channel mix')
        ->resize(390, 844)
        ->assertScript('document.documentElement.scrollWidth <= window.innerWidth', true)
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs()
        ->assertNoAccessibilityIssues();
});

test('the pricing reference updates prices in dark mode', function (): void {
    app(RolePermissionSeeder::class)->run();

    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $this->actingAs($admin);

    visit(route('admin.argonGallery.examples.show', 'general-pricing-page', absolute: false))
        ->inDarkMode()
        ->assertSee('$89')
        ->click('Annual billing')
        ->assertSee('$71')
        ->resize(390, 844)
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs()
        ->assertNoAccessibilityIssues();
});

test('the sweet alerts reference opens an accessible shared modal', function (): void {
    app(RolePermissionSeeder::class)->run();

    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $this->actingAs($admin);

    $page = visit(route('admin.argonGallery.examples.show', 'general-sweet-alerts', absolute: false))
        ->inDarkMode();
    $page->page()->getByTestId('sweet-alert-0')->press('Enter');

    $page->assertSee('A concise informational dialog.')
        ->assertNoAccessibilityIssues();

    $page->page()->getByRole('button', ['name' => 'Confirm', 'exact' => true])->press('Enter');

    $page->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});

test('the general gallery preserves rtl and empty-source states', function (): void {
    app(RolePermissionSeeder::class)->run();

    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $this->actingAs($admin);

    visit(route('admin.argonGallery.examples.show', 'general-rtl-page', absolute: false))
        ->assertSee('نظرة عامة على المبيعات')
        ->assertScript('document.querySelector("[dir=rtl]")?.dir', 'rtl')
        ->resize(390, 844)
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs()
        ->assertNoAccessibilityIssues();

    visit(route('admin.argonGallery.examples.show', 'general-tables-datatables', absolute: false))
        ->assertSee('DataTables source unavailable')
        ->assertSee('without inventing source content')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});
