<?php

declare(strict_types=1);

use App\Models\Contact;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function (): void {
    app(RolePermissionSeeder::class)->run();
});

function crmDetailBrowserAdmin(): User
{
    $user = User::factory()->create();
    $user->assignRole('Admin');

    return $user;
}

test('organization detail direct route renders cleanly', function (): void {
    $admin = crmDetailBrowserAdmin();
    $organization = Organization::factory()->create(['name' => 'Task 20 Direct Organization']);
    $path = route('crm.organizations.show', $organization, false);

    $this->actingAs($admin);

    visit($path)
        ->assertPathIs($path)
        ->assertSee($organization->name)
        ->assertSee('Contacts')
        ->assertSee('Leads')
        ->assertSee('0')
        ->assertSee('No contacts recorded.')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});

test('contact detail direct route renders cleanly', function (): void {
    $admin = crmDetailBrowserAdmin();
    $organization = Organization::factory()->create(['name' => 'Task 20 Contact Organization']);
    $contact = Contact::factory()->create([
        'name' => 'Task 20 Direct Contact',
        'organization_id' => $organization->id,
    ]);
    $path = route('crm.contacts.show', $contact, false);

    $this->actingAs($admin);

    visit($path)
        ->assertPathIs($path)
        ->assertSee($contact->name)
        ->assertSee($organization->name)
        ->assertSee('Related leads')
        ->assertSee('0')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});

test('organization list View link navigates to the UUID detail route', function (): void {
    $admin = crmDetailBrowserAdmin();
    $organization = Organization::factory()->create(['name' => 'Task 20 Organization Navigation']);
    $path = route('crm.organizations.show', $organization, false);

    $this->actingAs($admin);

    visit(route('crm.organizations.index', [], false))
        ->assertSee($organization->name)
        ->assertPresent("a[href=\"{$path}\"]")
        ->click("a[href=\"{$path}\"]")
        ->assertPathIs($path)
        ->assertSee($organization->name)
        ->assertSee('Contacts')
        ->assertSee('Leads')
        ->assertSee('0')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});

test('contact list View link navigates to the UUID detail route', function (): void {
    $admin = crmDetailBrowserAdmin();
    $organization = Organization::factory()->create(['name' => 'Task 20 List Contact Organization']);
    $contact = Contact::factory()->create([
        'name' => 'Task 20 Contact Navigation',
        'organization_id' => $organization->id,
    ]);
    $path = route('crm.contacts.show', $contact, false);

    $this->actingAs($admin);

    visit(route('crm.contacts.index', [], false))
        ->assertSee($contact->name)
        ->assertPresent("a[href=\"{$path}\"]")
        ->click("a[href=\"{$path}\"]")
        ->assertPathIs($path)
        ->assertSee($contact->name)
        ->assertSee($organization->name)
        ->assertSee('Related leads')
        ->assertSee('0')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});
