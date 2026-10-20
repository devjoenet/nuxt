<?php

declare(strict_types=1);

use App\Models\Contact;
use App\Models\Lead;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    $this->withoutVite();
});

function entityCrmUser(bool $admin = false): User
{
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::findOrCreate('crm.view'));
    $user->givePermissionTo(Permission::findOrCreate('crm.manage'));

    if ($admin) {
        $user->assignRole(Role::findOrCreate('Admin'));
    } else {
        $user->assignRole(Role::findOrCreate('Sales/Ops'));
    }

    return $user;
}

test('guests and users without CRM access cannot view entity lists', function (): void {
    $this->get(route('crm.organizations.index'))->assertRedirect('/login');
    $this->actingAs(User::factory()->create())->get(route('crm.contacts.index'))->assertForbidden();
});

test('sales ops see their owned and unassigned entities but not another users records', function (): void {
    $user = entityCrmUser();
    $ownedOrganization = Organization::factory()->create(['owner_id' => $user->id]);
    $otherOrganization = Organization::factory()->create(['owner_id' => User::factory()->create()->id]);
    $ownedContact = Contact::factory()->create(['owner_id' => $user->id]);
    $otherContact = Contact::factory()->create(['owner_id' => User::factory()->create()->id]);

    $this->actingAs($user)
        ->get(route('crm.organizations.index', ['owner' => $user->id]))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->has('organizations.data', 1)
            ->where('organizations.data.0.id', $ownedOrganization->uuid));

    $this->actingAs($user)
        ->get(route('crm.contacts.index', ['owner' => $user->id]))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->has('contacts.data', 1)
            ->where('contacts.data.0.id', $ownedContact->uuid));

    expect($otherOrganization->owner_id)->not->toBe($user->id)
        ->and($otherContact->owner_id)->not->toBe($user->id);
});

test('admins can see every CRM entity', function (): void {
    $admin = entityCrmUser(admin: true);
    $first = Organization::factory()->create(['name' => 'Alpha Account', 'owner_id' => User::factory()->create()->id]);
    $second = Organization::factory()->create(['name' => 'Beta Account', 'owner_id' => User::factory()->create()->id]);

    $this->actingAs($admin)
        ->get(route('crm.organizations.index', ['sort' => 'name', 'direction' => 'asc']))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->has('organizations.data', 2)
            ->where('organizations.data.0.id', $first->uuid)
            ->where('organizations.data.1.id', $second->uuid));
});

test('sales ops can view an organization with scoped contacts', function (): void {
    $user = entityCrmUser();
    $other = User::factory()->create();
    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $ownedContact = Contact::factory()->create([
        'organization_id' => $organization->id,
        'owner_id' => $user->id,
    ]);
    $unassignedContact = Contact::factory()->create([
        'organization_id' => $organization->id,
        'owner_id' => null,
    ]);
    Contact::factory()->create([
        'organization_id' => $organization->id,
        'owner_id' => $other->id,
    ]);

    $this->actingAs($user)
        ->get(route('crm.organizations.show', $organization))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Crm/Organizations/Show')
            ->where('organization.id', $organization->uuid)
            ->where('organization.name', $organization->name)
            ->where('organization.website', $organization->website)
            ->where('organization.owner.id', $user->id)
            ->where('organization.leads_count', 0)
            ->where('canManage', true)
            ->where('organization.contacts', fn (Collection $contacts): bool => $contacts->pluck('id')->sort()->values()->all() === collect([
                $ownedContact->uuid,
                $unassignedContact->uuid,
            ])->sort()->values()->all()));
});

test('sales ops can view a contact with flat resource props', function (): void {
    $user = entityCrmUser();
    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $contact = Contact::factory()->create([
        'organization_id' => $organization->id,
        'owner_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->get(route('crm.contacts.show', $contact))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Crm/Contacts/Show')
            ->where('contact.id', $contact->uuid)
            ->where('contact.email', $contact->email)
            ->where('contact.organization.id', $organization->uuid)
            ->where('contact.owner.id', $user->id)
            ->where('contact.leads_count', 0)
            ->where('canManage', true));
});

test('contact resources and filters hide inaccessible related organizations', function (): void {
    $user = entityCrmUser();
    $other = User::factory()->create();
    $hiddenOrganization = Organization::factory()->create([
        'owner_id' => $other->id,
        'name' => 'Hidden Organization Match',
    ]);
    $contact = Contact::factory()->create([
        'owner_id' => $user->id,
        'organization_id' => $hiddenOrganization->id,
    ]);

    $this->actingAs($user)
        ->get(route('crm.contacts.show', $contact))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('contact.organization', null));

    $this->actingAs($user)
        ->get(route('crm.contacts.index', ['search' => 'Hidden Organization Match']))
        ->assertInertia(fn (Assert $page): Assert => $page->has('contacts.data', 0));

    $this->actingAs($user)
        ->get(route('crm.contacts.index', ['organization' => $hiddenOrganization->uuid]))
        ->assertInertia(fn (Assert $page): Assert => $page->has('contacts.data', 0));
});

test('related CRM counts use the same visibility rule as parent records', function (): void {
    $user = entityCrmUser();
    $administrator = entityCrmUser(admin: true);
    $other = User::factory()->create();
    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $ownedContact = Contact::factory()->create(['organization_id' => $organization->id, 'owner_id' => $user->id]);
    $unassignedContact = Contact::factory()->create(['organization_id' => $organization->id, 'owner_id' => null]);
    Contact::factory()->create(['organization_id' => $organization->id, 'owner_id' => $other->id]);

    foreach ([$user, null, $other] as $owner) {
        Lead::factory()->create([
            'contact_id' => $ownedContact->id,
            'organization_id' => $organization->id,
            'owner_id' => $owner?->id,
        ]);
    }

    $this->actingAs($user)
        ->get(route('crm.organizations.index'))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('organizations.data.0.contacts_count', 2)
            ->where('organizations.data.0.leads_count', 2));

    $this->actingAs($user)
        ->get(route('crm.organizations.show', $organization))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('organization.contacts_count', 2)
            ->where('organization.leads_count', 2));

    $this->actingAs($user)
        ->get(route('crm.contacts.show', $ownedContact))
        ->assertInertia(fn (Assert $page): Assert => $page->where('contact.leads_count', 2));

    $this->actingAs($administrator)
        ->get(route('crm.organizations.show', $organization))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('organization.contacts_count', 3)
            ->where('organization.leads_count', 3));

    expect($unassignedContact->isVisibleTo($user))->toBeTrue();
});

test('entity index filters normalize false-like archived values and reject invalid data', function (): void {
    $user = entityCrmUser();
    $activeOrganization = Organization::factory()->create(['owner_id' => $user->id]);
    Organization::factory()->create(['owner_id' => $user->id, 'archived_at' => now()]);
    $contact = Contact::factory()->create(['owner_id' => $user->id]);

    foreach ([null, 0, '0', false] as $archived) {
        $this->actingAs($user)
            ->get(route('crm.organizations.index', ['archived' => $archived]))
            ->assertInertia(fn (Assert $page): Assert => $page
                ->where('organizations.data.0.id', $activeOrganization->uuid)
                ->where('organizations.meta.total', 2));
    }

    $this->actingAs($user)
        ->get(route('crm.contacts.index', ['search' => '  '.$contact->name.'  ']))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('filters.search', $contact->name)
            ->where('contacts.data.0.id', $contact->uuid));

    $this->actingAs($user)
        ->get(route('crm.organizations.index', ['search' => ['invalid']]))
        ->assertRedirect()
        ->assertSessionHasErrors('search');
    $this->actingAs($user)
        ->get(route('crm.contacts.index', ['organization' => 'not-a-uuid']))
        ->assertRedirect()
        ->assertSessionHasErrors('organization');
    $this->actingAs($user)
        ->get(route('crm.contacts.index', ['archived' => 'not-a-boolean']))
        ->assertRedirect()
        ->assertSessionHasErrors('archived');
    $this->actingAs($user)
        ->get(route('crm.organizations.index', ['sort' => 'owner_id']))
        ->assertRedirect()
        ->assertSessionHasErrors('sort');
});

test('out of scope direct URLs return not found', function (): void {
    $user = entityCrmUser();
    $other = User::factory()->create();
    $organization = Organization::factory()->create(['owner_id' => $other->id]);
    $contact = Contact::factory()->create(['owner_id' => $other->id]);

    $this->actingAs($user)->get(route('crm.organizations.show', $organization))->assertNotFound();
    $this->actingAs($user)->get(route('crm.contacts.show', $contact))->assertNotFound();
});

test('sales ops can create and update owned organizations and contacts', function (): void {
    $user = entityCrmUser();

    $this->actingAs($user)->post(route('crm.organizations.store'), [
        'name' => 'Southeast Logistics',
        'website' => 'https://southeast-logistics.example',
    ])->assertSessionHasNoErrors();

    $organization = Organization::query()->sole();
    expect($organization->owner_id)->toBe($user->id);

    $this->actingAs($user)->post(route('crm.contacts.store'), [
        'name' => 'Morgan Lee',
        'email' => 'morgan@southeast-logistics.example',
        'organization_id' => $organization->uuid,
    ])->assertSessionHasNoErrors();

    $contact = Contact::query()->sole();
    expect($contact->owner_id)->toBe($user->id)
        ->and($contact->organization_id)->toBe($organization->id);

    $this->actingAs($user)->patch(route('crm.contacts.update', $contact), [
        'name' => 'Morgan Updated',
        'email' => $contact->email,
        'organization_id' => $organization->uuid,
    ])->assertSessionHasNoErrors();

    expect($contact->refresh()->name)->toBe('Morgan Updated');
});

test('sales ops cannot assign or transfer ownership', function (): void {
    $user = entityCrmUser();
    $organization = Organization::factory()->create(['owner_id' => $user->id]);
    $other = User::factory()->create();

    $this->actingAs($user)
        ->patch(route('crm.organizations.update', $organization), [
            'name' => $organization->name,
            'website' => $organization->website,
            'owner_id' => $other->id,
        ])
        ->assertSessionHasErrors('owner_id');
});

test('admins can transfer ownership and users can archive or delete visible entities', function (): void {
    $admin = entityCrmUser(admin: true);
    expect(App\Support\CrmAccess::administrator($admin))->toBeTrue();
    $owner = User::factory()->create();
    $organization = Organization::factory()->create(['owner_id' => $owner->id]);
    $contact = Contact::factory()->create(['owner_id' => $admin->id]);

    $this->actingAs($admin)->patch(route('crm.organizations.update', $organization), [
        'name' => $organization->name,
        'website' => $organization->website,
        'owner_id' => (string) $admin->id,
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect($organization->refresh()->owner_id)->toBe($admin->id);

    $this->actingAs($admin)->post(route('crm.contacts.archive', $contact))->assertRedirect(route('crm.contacts.index'));
    expect($contact->refresh()->archived_at)->not->toBeNull();

    $this->actingAs($admin)->delete(route('crm.organizations.destroy', $organization))->assertRedirect(route('crm.organizations.index'));
    expect(Organization::query()->find($organization->id))->toBeNull();
});

test('CRM managers can open entity forms and complete the remaining lifecycle paths', function (): void {
    $admin = entityCrmUser(admin: true);
    $organization = Organization::factory()->create(['owner_id' => $admin->id]);
    $contact = Contact::factory()->create([
        'organization_id' => $organization->id,
        'owner_id' => $admin->id,
    ]);

    $this->actingAs($admin)
        ->get(route('crm.organizations.create'))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Crm/Organizations/Form')
            ->where('organization', null)
            ->where('canAssign', true));
    $this->actingAs($admin)
        ->get(route('crm.organizations.edit', $organization))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Crm/Organizations/Form')
            ->where('organization.id', $organization->uuid));
    $this->actingAs($admin)
        ->get(route('crm.contacts.create'))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Crm/Contacts/Form')
            ->where('contact', null)
            ->where('canAssign', true));
    $this->actingAs($admin)
        ->get(route('crm.contacts.edit', $contact))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Crm/Contacts/Form')
            ->where('contact.id', $contact->uuid));

    $this->actingAs($admin)
        ->post(route('crm.organizations.archive', $organization))
        ->assertRedirect(route('crm.organizations.index'));
    $this->actingAs($admin)
        ->delete(route('crm.contacts.destroy', $contact))
        ->assertRedirect(route('crm.contacts.index'));

    expect($organization->refresh()->archived_at)->not->toBeNull()
        ->and(Contact::query()->find($contact->id))->toBeNull();
});

test('authorized users restore archived organizations and contacts with DELETE', function (): void {
    $admin = entityCrmUser(admin: true);
    $organization = Organization::factory()->create(['owner_id' => $admin->id, 'archived_at' => now()]);
    $contact = Contact::factory()->create(['owner_id' => $admin->id, 'archived_at' => now()]);

    $this->actingAs($admin)
        ->delete(route('crm.organizations.restore', $organization))
        ->assertRedirect(route('crm.organizations.show', $organization));
    $this->actingAs($admin)
        ->delete(route('crm.contacts.restore', $contact))
        ->assertRedirect(route('crm.contacts.show', $contact));

    expect($organization->refresh()->archived_at)->toBeNull()
        ->and($contact->refresh()->archived_at)->toBeNull();
});

test('entity lists filter and paginate on the server', function (): void {
    $user = entityCrmUser();
    Organization::factory()->count(16)->create(['owner_id' => $user->id]);
    $match = Organization::factory()->create(['name' => 'Searchable Account', 'owner_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('crm.organizations.index', ['search' => 'Searchable']))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->has('organizations.data', 1)
            ->where('organizations.data.0.id', $match->uuid)
            ->where('organizations.meta.total', 1));

    $this->actingAs($user)
        ->get(route('crm.contacts.index', ['page' => 2]))
        ->assertInertia(fn (Assert $page): Assert => $page->where('contacts.meta.current_page', 2));
});
