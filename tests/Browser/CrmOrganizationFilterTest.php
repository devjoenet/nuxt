<?php

declare(strict_types=1);

use App\Enums\RoleName;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use SoutheastCode\Crm\Organizations\Models\Organization;

test('admins can clear the organization owner filter on a narrow viewport', function (): void {
    app(RolePermissionSeeder::class)->run();

    $admin = User::factory()->create();
    $admin->assignRole(RoleName::Admin->value);
    $firstOwner = User::factory()->create(['name' => 'Task 20 Filter Owner Alpha']);
    $secondOwner = User::factory()->create(['name' => 'Task 20 Filter Owner Beta']);
    $search = 'Task 20 Owner Filter';
    $firstOrganization = Organization::factory()->create([
        'name' => "{$search} Alpha Account",
        'owner_id' => $firstOwner->id,
    ]);
    $secondOrganization = Organization::factory()->create([
        'name' => "{$search} Beta Account",
        'owner_id' => $secondOwner->id,
    ]);
    Organization::factory()->create(['name' => 'Unrelated Organization']);

    $this->actingAs($admin);

    $page = visit(route('crm.organizations.index', ['search' => $search], false))
        ->resize(390, 844)
        ->assertSee($firstOrganization->name)
        ->assertSee($secondOrganization->name)
        ->assertSee('All owners')
        ->assertQueryStringHas('search', $search)
        ->assertQueryStringMissing('owner')
        ->assertMissing('button[aria-label="Clear owner filter"]')
        ->assertScript('document.documentElement.scrollWidth <= window.innerWidth', true);

    $page->page()->getByRole('combobox', ['name' => 'Owner', 'exact' => true])->click();
    $page->page()->getByRole('option', ['name' => $firstOwner->name, 'exact' => true])->click();

    $page
        ->assertQueryStringHas('search', $search)
        ->assertQueryStringHas('owner', (string) $firstOwner->id)
        ->assertSee($firstOrganization->name)
        ->assertDontSee($secondOrganization->name)
        ->assertVisible('button[aria-label="Clear owner filter"]')
        ->click('button[aria-label="Clear owner filter"]')
        ->assertQueryStringHas('search', $search)
        ->assertQueryStringMissing('owner')
        ->assertSee($firstOrganization->name)
        ->assertSee($secondOrganization->name)
        ->assertSee('All owners')
        ->assertMissing('button[aria-label="Clear owner filter"]')
        ->clear('#organization-search')
        ->type('#organization-search', 'No matching organization should exist')
        ->wait(0.5)
        ->assertQueryStringHas('search', 'No matching organization should exist')
        ->assertSee('No organizations found')
        ->assertSee('Reset filters')
        ->click('Reset filters')
        ->assertQueryStringMissing('search')
        ->assertSee($firstOrganization->name)
        ->assertSee($secondOrganization->name)
        ->assertScript('document.documentElement.scrollWidth <= window.innerWidth', true)
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});

test('organization pagination preserves filters across browser history', function (): void {
    app(RolePermissionSeeder::class)->run();

    $admin = User::factory()->create();
    $admin->assignRole(RoleName::Admin->value);
    $search = 'Index history account';

    Organization::factory()->count(17)->create([
        'name' => $search,
        'owner_id' => $admin->id,
    ]);

    $this->actingAs($admin);

    $page = visit(route('crm.organizations.index', ['search' => $search], false))
        ->assertSee('17 organizations')
        ->assertQueryStringHas('search', $search)
        ->assertQueryStringMissing('page');

    $page->page()->locator('[data-testid="index-pagination"] [data-slot="item"]')->nth(1)->click();

    $page->wait(0.5)
        ->assertQueryStringHas('search', $search)
        ->assertQueryStringHas('page', '2');

    $page->page()->back();

    $page->wait(0.5)
        ->assertQueryStringHas('search', $search)
        ->assertQueryStringMissing('page')
        ->assertSee('17 organizations');

    $page->page()->forward();

    $page->wait(0.5)
        ->assertQueryStringHas('search', $search)
        ->assertQueryStringHas('page', '2')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});
