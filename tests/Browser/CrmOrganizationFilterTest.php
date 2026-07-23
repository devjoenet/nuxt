<?php

declare(strict_types=1);

use App\Enums\RoleName;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

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
        ->assertScript('document.documentElement.scrollWidth <= window.innerWidth', true)
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});
