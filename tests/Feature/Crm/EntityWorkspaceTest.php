<?php

declare(strict_types=1);

use App\Models\Contact;
use App\Models\Organization;
use App\Models\User;
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
        'organization_id' => $organization->id,
    ])->assertSessionHasNoErrors();

    $contact = Contact::query()->sole();
    expect($contact->owner_id)->toBe($user->id)
        ->and($contact->organization_id)->toBe($organization->id);

    $this->actingAs($user)->patch(route('crm.contacts.update', $contact), [
        'name' => 'Morgan Updated',
        'email' => $contact->email,
        'organization_id' => $organization->id,
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
    expect($admin->isCrmAdministrator())->toBeTrue();
    $owner = User::factory()->create();
    $organization = Organization::factory()->create(['owner_id' => $owner->id]);
    $contact = Contact::factory()->create(['owner_id' => $admin->id]);

    $this->actingAs($admin)->patch(route('crm.organizations.update', $organization), [
        'name' => $organization->name,
        'website' => $organization->website,
        'owner_id' => $admin->id,
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect($organization->refresh()->owner_id)->toBe($admin->id);

    $this->actingAs($admin)->post(route('crm.contacts.archive', $contact))->assertRedirect(route('crm.contacts.index'));
    expect($contact->refresh()->archived_at)->not->toBeNull();

    $this->actingAs($admin)->delete(route('crm.organizations.destroy', $organization))->assertRedirect(route('crm.organizations.index'));
    expect(Organization::query()->find($organization->id))->toBeNull();
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
