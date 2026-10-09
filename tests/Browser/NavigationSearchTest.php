<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

test('collapsed workspace groups release their height and restore their links', function (): void {
    app(RolePermissionSeeder::class)->run();

    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $this->actingAs($admin);

    $this->withSession(['auth.password_confirmed_at' => now()->timestamp]);

    visit('/dashboard')
        ->assertScript('Boolean((window.adminGroup = Array.from(document.querySelectorAll(\'nav[aria-label="Primary workspace navigation"] button[data-slot="link"]\')).find(button => button.textContent.trim() === "Admin")?.closest(\'[data-slot="item"]\')))', true)
        ->assertVisible('nav a[href="/admin/users"]')
        ->click('nav button[data-slot="link"]:has-text("Admin")')
        ->assertScript('window.adminGroup.querySelector(\'[data-slot="content"]\').getBoundingClientRect().height', 0)
        ->assertMissing('nav a[href="/admin/users"]')
        ->click('nav button[data-slot="link"]:has-text("Admin")')
        ->assertVisible('nav a[href="/admin/users"]')
        ->assertScript('window.adminGroup.querySelector(\'[data-slot="content"]\').getBoundingClientRect().height > 0', true)
        ->assertPresent('nav[aria-label="Primary workspace navigation"] a[href="/settings/profile"]')
        ->click('nav a[href="/settings/profile"]')
        ->assertPathIs('/settings/profile')
        ->assertAriaAttribute('nav[aria-label="Primary workspace navigation"] a[href="/settings/profile"]', 'current', 'page')
        ->click('nav[aria-label="Primary workspace navigation"] a[href="/settings/security"]')
        ->assertPathIs('/settings/security')
        ->assertAriaAttribute('nav[aria-label="Primary workspace navigation"] a[href="/settings/security"]', 'current', 'page')
        ->assertNoJavaScriptErrors();
});

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
        ->assertPresent('a[data-slot="item"][href="/crm/leads"][data-highlighted]')
        ->keys($searchInput, 'Enter')
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

    $this->withSession(['auth.password_confirmed_at' => now()->timestamp]);

    visit(route('dashboard', absolute: false))
        ->resize(390, 844)
        ->click('button[aria-label="Open sidebar"]')
        ->assertVisible('[role="dialog"]')
        ->assertScript('document.activeElement?.closest("[role=dialog]") !== null', true)
        ->assertPresent('[role="dialog"] nav[aria-label="Primary workspace navigation"]')
        ->assertPresent('[role="dialog"] nav[aria-label="Primary workspace navigation"] a[href="/settings/profile"]')
        ->keys('[role="dialog"]', 'Escape')
        ->assertMissing('[role="dialog"]')
        ->assertScript('document.activeElement?.getAttribute("aria-label")', 'Open sidebar')
        ->click('button[aria-label="Open sidebar"]')
        ->click("[role=\"dialog\"] a[href=\"{$leadsPath}\"]")
        ->assertPathIs($leadsPath)
        ->assertMissing('[role="dialog"]')
        ->click('button[aria-label="Open sidebar"]')
        ->click('[role="dialog"] nav a[href="/settings/security"]')
        ->assertPathIs('/settings/security')
        ->assertMissing('[role="dialog"]')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});

test('appearance controls persist both explicit themes across navigation and reloads', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user);

    $page = visit(route('dashboard', absolute: false))
        ->click('[data-testid="appearance-menu-trigger"]')
        ->click('Dark')
        ->assertScript('document.documentElement.classList.contains("dark")', true)
        ->assertScript('document.documentElement.dataset.appearance', 'dark')
        ->assertScript('document.cookie.includes("appearance=dark")', true)
        ->click('a[data-slot="link"][href="/settings/profile"]')
        ->assertPathIs('/settings/profile')
        ->assertScript('document.documentElement.classList.contains("dark")', true);

    $page->page()->reload();

    $page->wait(0.3)
        ->assertScript('document.documentElement.classList.contains("dark")', true)
        ->click('[data-testid="appearance-menu-trigger"]')
        ->click('Light')
        ->assertScript('document.documentElement.classList.contains("dark")', false)
        ->assertScript('document.documentElement.dataset.appearance', 'light')
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
