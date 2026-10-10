<?php

declare(strict_types=1);

use App\Models\Contact;
use App\Models\Lead;
use App\Models\Organization;
use App\Models\User;
use Spatie\Permission\Models\Permission;

test('lead list identities and lead details support keyboard navigation without a View column', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::findOrCreate('crm.view'));
    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $contact = Contact::factory()->create(['owner_id' => $user->id]);
    $lead = Lead::factory()->create(['owner_id' => $user->id, 'contact_id' => $contact->id, 'organization_id' => $organization->id]);
    $this->actingAs($user);

    foreach (['crm.contacts.show' => $contact, 'crm.organizations.show' => $organization, 'crm.leads.show' => $lead] as $route => $record) {
        $page = visit(route('crm.leads.index', [], false))
            ->assertSee($contact->name)
            ->assertSee($organization->name)
            ->assertScript('document.querySelectorAll("tbody tr:first-child td").length', 5)
            ->assertMissing('tbody a:text-is("View")')
            ->assertScript('document.querySelector(\'tbody a[href="'.route('crm.leads.show', $lead, false).'\"]\')?.textContent', $contact->name)
            ->assertScript('document.querySelector(\'tbody a[href="'.route('crm.contacts.show', $contact, false).'\"]\')?.textContent', 'View Contact');
        $link = $page->page()->locator('tbody a[href="'.route($route, $record, false).'"]');
        $link->focus();
        $link->press('Enter');

        $page->assertPathIs(route($route, $record, false))->assertNoJavaScriptErrors();
    }
});

test('lead details remain reachable for missing hidden and deleted related records', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::findOrCreate('crm.view'));
    $other = User::factory()->create();
    $hiddenContact = Contact::factory()->create(['owner_id' => $other->id]);
    $hiddenOrganization = Organization::factory()->create(['owner_id' => $other->id]);
    $hiddenLead = Lead::factory()->create(['owner_id' => $user->id, 'contact_id' => $hiddenContact->id, 'organization_id' => $hiddenOrganization->id]);
    $deletedContact = Contact::factory()->create(['owner_id' => $user->id]);
    $deletedContact->delete();
    $deletedLead = Lead::factory()->create(['owner_id' => $user->id, 'contact_id' => $deletedContact->id]);
    $this->actingAs($user);

    visit(route('crm.leads.index', [], false))
        ->resize(390, 844)
        ->assertSee('Contact unavailable')
        ->assertSee('Independent')
        ->assertSee('Deleted contact')
        ->assertMissing('tbody a[href="'.route('crm.contacts.show', $hiddenContact, false).'"]')
        ->assertMissing('tbody a[href="'.route('crm.contacts.show', $deletedContact, false).'"]')
        ->assertMissing('tbody a[href="'.route('crm.organizations.show', $hiddenOrganization, false).'"]')
        ->assertPresent('tbody a[href="'.route('crm.leads.show', $deletedLead, false).'"]')
        ->click('tbody a[href="'.route('crm.leads.show', $hiddenLead, false).'"]')
        ->assertPathIs(route('crm.leads.show', $hiddenLead, false))
        ->assertNoJavaScriptErrors();
});
