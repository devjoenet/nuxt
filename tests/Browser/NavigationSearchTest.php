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
        ->assertNoConsoleLogs();

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
