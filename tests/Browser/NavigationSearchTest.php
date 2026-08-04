<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

test('authorized users can search nested navigation and page headings', function (): void {
    app(RolePermissionSeeder::class)->run();

    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $this->actingAs($admin);

    $searchInput = 'input[placeholder="Type a command or search…"]';

    $page = visit('/dashboard')
        ->click('Search…')
        ->fill($searchInput, 'Leads')
        ->assertSee('Leads')
        ->assertSee('CRM')
        ->assertPresent('a[data-slot="item"][href="/crm/leads"]')
        ->keys($searchInput, ['ArrowDown', 'Enter'])
        ->assertPathIs('/crm/leads')
        ->click('Search…')
        ->fill($searchInput, 'Roles and permissions')
        ->assertSee('Roles')
        ->assertSee('Roles and permissions')
        ->assertPresent('a[data-slot="item"][href="/admin/roles"]')
        ->fill($searchInput, 'Recent activity')
        ->assertSee('Overview')
        ->assertSee('Recent activity')
        ->assertPresent('a[data-slot="item"][href="/admin"]')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs()
        ->assertNoAccessibilityIssues();

    $page->assertPathIs('/crm/leads');
});

test('search excludes navigation destinations the user cannot access', function (): void {
    app(RolePermissionSeeder::class)->run();

    $clientViewer = User::factory()->create();
    $clientViewer->assignRole('Client Viewer');

    $this->actingAs($clientViewer);

    visit('/dashboard')
        ->click('Search…')
        ->fill('input[placeholder="Type a command or search…"]', 'Leads')
        ->assertMissing('a[data-slot="item"][href="/crm/leads"]')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});

test('the workspace layout persists while navigation keeps its active state', function (): void {
    app(RolePermissionSeeder::class)->run();

    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $this->actingAs($admin);

    $adminPath = route('admin.dashboard', absolute: false);

    visit(route('dashboard', absolute: false))
        ->assertPresent('[data-testid="workspace-sidebar"]')
        ->assertScript('Boolean((window.workspaceSidebar = document.querySelector(\'[data-testid="workspace-sidebar"]\')))', true)
        ->click("a[data-slot=\"link\"][href=\"{$adminPath}\"]")
        ->assertPathIs($adminPath)
        ->assertAriaAttribute("a[data-slot=\"link\"][href=\"{$adminPath}\"]", 'current', 'page')
        ->assertScript('window.workspaceSidebar === document.querySelector(\'[data-testid="workspace-sidebar"]\')', true)
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});

test('mobile navigation closes after selecting a destination', function (): void {
    app(RolePermissionSeeder::class)->run();

    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $this->actingAs($admin);

    $leadsPath = route('crm.leads.index', absolute: false);

    visit(route('dashboard', absolute: false))
        ->resize(390, 844)
        ->click('button[aria-label="Open sidebar"]')
        ->assertVisible('[role="dialog"]')
        ->assertPresent('[role="dialog"] nav[aria-label="Primary workspace navigation"]')
        ->assertPresent('[role="dialog"] nav[aria-label="Account navigation"]')
        ->click("[role=\"dialog\"] a[href=\"{$leadsPath}\"]")
        ->assertPathIs($leadsPath)
        ->assertMissing('[role="dialog"]')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});

test('the account menu logs the current user out', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user);

    visit(route('dashboard', absolute: false))
        ->click('[data-testid="account-menu-trigger"]')
        ->assertVisible('[role="menu"]')
        ->click('Log out')
        ->assertPathIs(route('home', absolute: false))
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});
