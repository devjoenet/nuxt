<?php

declare(strict_types=1);

use App\Enums\RoleName;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use SoutheastCode\Crm\Organizations\Models\Organization;

beforeEach(function (): void {
    app(RolePermissionSeeder::class)->run();
});

function crmFormBrowserAdmin(): User
{
    $admin = User::factory()->create();
    $admin->assignRole(RoleName::Admin->value);

    return $admin;
}

test('organization create form presents validation and submit state on mobile', function (): void {
    $admin = crmFormBrowserAdmin();
    Organization::factory()->create(['website' => 'https://existing.example']);

    $this->actingAs($admin);

    $page = visit(route('crm.organizations.create', [], false))
        ->resize(390, 844)
        ->assertTitle('New organization - Southeast Code')
        ->assertSee('Organization details')
        ->wait(0.2)
        ->assertScript('document.querySelectorAll(\'form\').length', 1)
        ->assertMissing('[data-testid="form-error-summary"]')
        ->fill('name', 'New mobile organization')
        ->fill('website', 'https://existing.example')
        ->click('Create organization')
        ->assertPresent('[data-testid="form-error-summary"]')
        ->assertSee('The website has already been taken.')
        ->assertAttribute('input[name="website"]', 'aria-invalid', 'true')
        ->assertScript("document.querySelector('input[name=\"website\"]')?.getAttribute('aria-describedby') === document.querySelector('input[name=\"website\"]')?.closest('.form-field')?.querySelector('[role=\"alert\"]')?.id", true)
        ->assertPathIs(route('crm.organizations.create', [], false))
        ->assertScript('document.documentElement.scrollWidth <= window.innerWidth', true)
        ->fill('website', 'https://new-mobile.example');

    $page->script('() => { const send = XMLHttpRequest.prototype.send; XMLHttpRequest.prototype.send = function (...args) { window.setTimeout(() => send.apply(this, args), 300); }; }');

    $page->click('Create organization')
        ->assertPresent('form[inert]')
        ->assertDisabled('button[type="submit"]')
        ->wait(0.5)
        ->assertSee('New mobile organization')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();

    $organization = Organization::query()->where('name', 'New mobile organization')->firstOrFail();

    $page->assertPathIs(route('crm.organizations.show', $organization, false));
});

test('organization form supports keyboard cancel navigation and editing', function (): void {
    $admin = crmFormBrowserAdmin();
    $organization = Organization::factory()->create(['name' => 'Original organization name']);

    $this->actingAs($admin);

    $createPage = visit(route('crm.organizations.create', [], false));
    $cancelLink = $createPage->page()->getByRole('link', ['name' => 'Cancel', 'exact' => true]);
    $cancelLink->focus();

    $createPage
        ->assertScript('document.activeElement?.textContent?.trim()', 'Cancel');

    $cancelLink->press('Enter');

    $createPage
        ->assertPathIs(route('crm.organizations.index', [], false))
        ->assertSee('Organizations');

    visit(route('crm.organizations.edit', $organization, false))
        ->assertSee('Organization details')
        ->assertScript("document.querySelector('input[name=\"name\"]')?.value", 'Original organization name')
        ->fill('name', 'Updated organization name')
        ->click('Save changes')
        ->assertPathIs(route('crm.organizations.show', $organization, false))
        ->assertSee('Updated organization name')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();

    expect($organization->refresh()->name)->toBe('Updated organization name');
});
