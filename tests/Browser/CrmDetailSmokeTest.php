<?php

declare(strict_types=1);

use App\Models\Contact;
use App\Models\Lead;
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

test('organization detail presents its summary, contacts, and responsive lifecycle controls', function (): void {
    $admin = crmDetailBrowserAdmin();
    $organization = Organization::factory()->create(['name' => 'Task 21 Direct Organization', 'website' => 'https://task-21.example']);
    $contact = Contact::factory()->create(['organization_id' => $organization->id, 'name' => 'Task 21 Contact']);
    $path = route('crm.organizations.show', $organization, false);

    $this->actingAs($admin);

    visit($path)
        ->assertTitle('Task 21 Direct Organization - Southeast Code')
        ->assertPathIs($path)
        ->assertCount('main[data-workspace-page-content]', 1)
        ->assertDataAttribute('main[data-workspace-page-content]', 'content-width', 'wide')
        ->assertSee($organization->name)
        ->assertPresent('[data-testid="organization-summary"] h1')
        ->assertPresent('a[href="https://task-21.example"]')
        ->assertSee('Contacts')
        ->assertPresent('[data-testid="organization-contacts"]')
        ->assertSee($contact->name)
        ->assertPresent("a[href=\"mailto:{$contact->email}\"]")
        ->assertSee('Leads')
        ->assertSee('Account details')
        ->assertSee('Archive')
        ->resize(390, 844)
        ->assertScript('document.documentElement.scrollWidth <= window.innerWidth', true)
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();

    visit($path)
        ->inDarkMode()
        ->assertSee($organization->name)
        ->assertScript('document.documentElement.classList.contains("dark")', true)
        ->assertScript('document.documentElement.scrollWidth <= window.innerWidth', true)
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});

test('contact detail presents relationship context in both color modes', function (): void {
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
        ->assertPresent('[data-testid="contact-summary"] h1')
        ->assertSee($organization->name)
        ->assertSee('Contact information')
        ->assertPresent("a[href=\"mailto:{$contact->email}\"]")
        ->assertPresent('a[href="'.route('crm.organizations.show', $organization, false).'"]')
        ->assertSee('Related leads')
        ->assertSee('Relationship details')
        ->resize(390, 844)
        ->assertScript('document.documentElement.scrollWidth <= window.innerWidth', true)
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();

    visit($path)
        ->inDarkMode()
        ->assertSee($contact->name)
        ->assertScript('document.documentElement.classList.contains("dark")', true)
        ->assertScript('document.documentElement.scrollWidth <= window.innerWidth', true)
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

test('archived detail records restore with DELETE requests', function (): void {
    $admin = crmDetailBrowserAdmin();
    $organization = Organization::factory()->create(['archived_at' => now()]);
    $contact = Contact::factory()->create(['archived_at' => now()]);

    $this->actingAs($admin);

    $organizationPage = visit(route('crm.organizations.show', $organization, false))
        ->assertPresent('[data-testid="organization-archived-status"]')
        ->click('Restore')
        ->assertSee('Restore this organization?')
        ->click('Cancel')
        ->assertPresent('[data-testid="organization-archived-status"]')
        ->assertScript('document.activeElement?.textContent?.trim()', 'Restore')
        ->click('Restore')
        ->pressAndWaitFor('Restore organization')
        ->assertMissing('[data-testid="organization-archived-status"]')
        ->assertSee('Organization restored.')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();

    visit(route('crm.contacts.show', $contact, false))
        ->assertPresent('[data-testid="contact-archived-status"]')
        ->click('Restore')
        ->assertSee('Restore this contact?')
        ->pressAndWaitFor('Restore contact')
        ->assertMissing('[data-testid="contact-archived-status"]')
        ->assertSee('Contact restored.')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();

    expect($organization->refresh()->archived_at)->toBeNull()
        ->and($contact->refresh()->archived_at)->toBeNull();
});

test('lead detail preserves deleted contact history without a contact link', function (): void {
    $admin = crmDetailBrowserAdmin();
    $visibleContact = Contact::factory()->create(['name' => 'Task 22 Visible Contact']);
    $visibleLead = Lead::factory()->create(['contact_id' => $visibleContact->id]);
    $contact = Contact::factory()->create(['name' => 'Task 22 Deleted Contact']);
    $contact->delete();
    $lead = Lead::factory()->create(['contact_id' => $contact->id]);
    $path = route('crm.leads.show', $lead, false);

    $this->actingAs($admin);

    visit(route('crm.leads.index', [], false))
        ->assertSee('Task 22 Visible Contact')
        ->assertSee('Task 22 Deleted Contact')
        ->assertSee('Deleted contact')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();

    visit(route('crm.leads.show', $visibleLead, false))
        ->assertSee('Task 22 Visible Contact')
        ->assertPresent('a[href="'.route('crm.contacts.show', $visibleContact, false).'"]')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();

    visit($path)
        ->assertPathIs($path)
        ->assertSee('Task 22 Deleted Contact')
        ->assertSee('Deleted contact')
        ->assertPresent('[data-testid="lead-deleted-contact-status"]')
        ->assertMissing('a[href="'.route('crm.contacts.show', $contact, false).'"]')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});
