<?php

declare(strict_types=1);

use App\Enums\RoleName;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

test('admins can select, update, and keyboard-operate one role permission editor', function (): void {
    app(RolePermissionSeeder::class)->run();

    $admin = User::factory()->create();
    $admin->assignRole(RoleName::Admin->value);
    $adminPermissionCount = Role::findByName(RoleName::Admin->value)->permissions()->count();

    $salesRole = Role::findByName(RoleName::SalesOps->value);
    $crmView = Permission::findByName('crm.view');
    $jobsView = Permission::findByName('jobs.view');
    $salesRole->syncPermissions([$crmView]);

    $this->actingAs($admin);

    $page = visit('/admin/roles')
        ->assertSee('Roles and permissions')
        ->assertScript('document.querySelectorAll("#role-selector").length', 1)
        ->assertScript('document.querySelectorAll("[data-testid=\"role-permission-list\"]").length', 1)
        ->assertScript('document.querySelectorAll("[data-testid=\"role-permission-list\"] fieldset").length', 6)
        ->assertScript('document.querySelectorAll("[data-testid^=\"role-permission-list-\"][data-testid$=\"-trigger\"]").length', 6)
        ->assertScript('Array.from(document.querySelectorAll("[data-testid=\"role-permission-list\"] legend")).map((legend) => legend.textContent?.trim()).join("|")', 'Admin|Audit log|CRM|Development|Jobs & queues|Business settings')
        ->assertScript('document.querySelectorAll("[data-testid=\"role-permission-list\"] input:checked").length', $adminPermissionCount)
        ->click('#role-selector')
        ->assertAttribute('#role-selector', 'aria-expanded', 'true')
        ->keys('#role-selector', 'Escape')
        ->assertAttribute('#role-selector', 'aria-expanded', 'false')
        ->click('#role-selector');

    $page->page()->getByRole('option', ['name' => RoleName::SalesOps->value, 'exact' => true])->click();

    $page->wait(0.2)
        ->assertSee(RoleName::SalesOps->value)
        ->assertScript('document.querySelectorAll("[data-testid=\"role-permission-list\"] input:checked").length', 1)
        ->check('[data-testid="role-permission-list"] input[value="'.$jobsView->getKey().'"]')
        ->assertAttribute('[data-testid="role-permission-list-crm-trigger"]', 'aria-expanded', 'true')
        ->click('[data-testid="role-permission-list-crm-trigger"]')
        ->assertAttribute('[data-testid="role-permission-list-crm-trigger"]', 'aria-expanded', 'false')
        ->click('Save permissions')
        ->wait(0.5)
        ->assertSee('Role permissions updated.')
        ->resize(320, 568)
        ->assertScript('document.documentElement.scrollWidth <= window.innerWidth', true)
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs()
        ->assertNoAccessibilityIssues();

    expect($salesRole->refresh()->permissions->pluck('name')->sort()->values()->all())
        ->toBe(collect([$crmView->name, $jobsView->name])->sort()->values()->all());
});

test('admins can create a role with initial permissions', function (): void {
    app(RolePermissionSeeder::class)->run();

    $admin = User::factory()->create();
    $admin->assignRole(RoleName::Admin->value);
    $crmView = Permission::findByName('crm.view');
    $jobsView = Permission::findByName('jobs.view');

    $this->actingAs($admin);

    $page = visit('/admin/roles')
        ->assertSee('New role')
        ->click('New role')
        ->assertSee('Create a role')
        ->resize(320, 568)
        ->assertScript('document.documentElement.scrollWidth <= window.innerWidth', true)
        ->assertScript('document.querySelectorAll("[data-testid=\"create-role-permission-list\"] fieldset").length', 6)
        ->assertScript('Array.from(document.querySelectorAll("[data-testid=\"create-role-permission-list\"] legend")).map((legend) => legend.textContent?.trim()).join("|")', 'Admin|Audit log|CRM|Development|Jobs & queues|Business settings')
        ->assertNoAccessibilityIssues()
        ->fill('input[name="name"]', 'Project Manager')
        ->check('[data-testid="create-role-permission-list"] input[value="'.$crmView->getKey().'"]')
        ->check('[data-testid="create-role-permission-list"] input[value="'.$jobsView->getKey().'"]')
        ->click('Create role')
        ->wait(0.5)
        ->assertSee('Role created.')
        ->click('Close')
        ->click('#role-selector');

    $page->page()->getByRole('option', ['name' => 'Project Manager', 'exact' => true])->click();

    $page->wait(0.2)
        ->assertScript('document.querySelectorAll("[data-testid=\\"role-permission-list\\"] input:checked").length', 2)
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();

    expect(Role::findByName('Project Manager')->permissions->pluck('name')->sort()->values()->all())
        ->toBe(collect([$crmView->name, $jobsView->name])->sort()->values()->all());
});

test('admin role cards retain visible hierarchy in dark mode', function (): void {
    app(RolePermissionSeeder::class)->run();

    $admin = User::factory()->create();
    $admin->assignRole(RoleName::Admin->value);

    $this->actingAs($admin);

    visit('/admin/roles')
        ->inDarkMode()
        ->assertScript('getComputedStyle(document.querySelector("[data-testid=role-editor-card]")).backgroundColor !== getComputedStyle(document.querySelector("[data-testid=role-permission-list-admin]")).backgroundColor', true)
        ->assertScript('getComputedStyle(document.querySelector("[data-testid=role-editor-card]")).boxShadow !== "none"', true)
        ->assertScript('getComputedStyle(document.querySelector("[data-testid=role-permission-list-admin]")).boxShadow !== "none"', true)
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});

test('super admins see a protected information state across narrow and dark layouts', function (): void {
    app(RolePermissionSeeder::class)->run();

    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(RoleName::SuperAdmin->value);

    $this->actingAs($superAdmin);

    $page = visit('/admin/roles')
        ->inLightMode()
        ->click('#role-selector');

    $page->page()->getByRole('option', ['name' => RoleName::SuperAdmin->value, 'exact' => true])->click();

    $page
        ->wait(0.2)
        ->resize(320, 568)
        ->assertSee('All permissions are granted to Super Admin by the server-side Gate bypass. This role is not editable from the UI.')
        ->assertMissing('form[action*="_method=PATCH"]')
        ->assertMissing('[data-testid="role-permission-list"]')
        ->assertScript('document.documentElement.scrollWidth <= window.innerWidth', true)
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs()
        ->assertNoAccessibilityIssues();

    $page = visit('/admin/roles')
        ->inDarkMode()
        ->click('#role-selector');

    $page->page()->getByRole('option', ['name' => RoleName::SuperAdmin->value, 'exact' => true])->click();

    $page
        ->wait(0.2)
        ->assertSee('All permissions are granted to Super Admin by the server-side Gate bypass. This role is not editable from the UI.')
        ->assertMissing('form[action*="_method=PATCH"]')
        ->assertMissing('[data-testid="role-permission-list"]')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});
