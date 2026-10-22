<?php

declare(strict_types=1);

use App\Enums\RoleName;
use App\Models\DevelopmentTask;
use App\Models\User;
use App\Models\UserTestingPage;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UserTestingPageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use SoutheastCode\Crm\Contacts\Models\Contact;
use SoutheastCode\Crm\Leads\Models\Lead;
use SoutheastCode\Crm\Organizations\Models\Organization;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

it('keeps the legacy checklist available to authorized users until cleanup', function (): void {
    $this->seed(UserTestingPageSeeder::class);
    $user = User::factory()->create()->assignRole(RoleName::Admin->value);

    $this->actingAs($user)
        ->get(route('development.userTesting.legacy'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->component('Development/UserTesting/Legacy')->has('pages', 30));
});

it('resolves database-backed checklist links to the first visible records', function (): void {
    $this->seed(UserTestingPageSeeder::class);
    $user = User::factory()->create()->assignRole(RoleName::Admin->value);
    $organization = Organization::factory()->create();
    $contact = Contact::factory()->for($organization)->create();
    $lead = Lead::factory()->for($contact)->create(['organization_id' => $organization->getKey()]);

    $this->actingAs($user)
        ->get(route('development.userTesting.legacy'))
        ->assertInertia(fn ($page) => $page
            ->where('pages.0.url', route('home', [], false))
            ->where('pages.0.roadmap_task_number', 14)
            ->where('pages.0.status', 'Needs verification')
            ->where('pages.1.url', route('privacy', [], false))
            ->where('pages.1.roadmap_task_number', 15)
            ->where('pages.11.url', route('crm.leads.show', $lead, false))
            ->where('pages.11.roadmap_task_number', 22)
            ->where('pages.11.status', 'Needs verification')
            ->where('pages.12.url', route('crm.leads.edit', $lead, false))
            ->where('pages.13.roadmap_task_number', 20)
            ->where('pages.13.status', 'Needs verification')
            ->where('pages.15.url', route('crm.organizations.show', $organization, false))
            ->where('pages.15.roadmap_task_number', 21)
            ->where('pages.15.status', 'Needs verification')
            ->where('pages.19.url', route('crm.contacts.show', $contact, false))
            ->where('pages.19.status', 'Needs verification')
            ->where('pages.19.roadmap_task_number', 21)
            ->where('pages.22.roadmap_task_number', 18)
            ->where('pages.23.roadmap_task_number', 19)
            ->where('pages.26.roadmap_task_number', 17)
            ->where('pages.21.url', route('admin.dashboard', [], false))
            ->where('pages.28.url', route('security.show', [], false))
            ->where('pages.29.roadmap_task_number', 16));
});

it('saves a review without removing the page from the queue', function (): void {
    $user = User::factory()->create()->assignRole(RoleName::Admin->value);
    $page = UserTestingPage::factory()->create();

    $this->actingAs($user)
        ->patch(route('development.userTesting.update', $page), [
            'tested' => '1',
            'notes' => 'Check the mobile navigation spacing before release.',
        ])
        ->assertRedirect(route('development.userTesting.legacy'));

    expect($page->refresh())
        ->tested_by->toBe($user->getKey())
        ->notes->toBe('Check the mobile navigation spacing before release.')
        ->tested_at->not->toBeNull()
        ->approved_at->toBeNull()
        ->roadmap_task_at->toBeNull();

    $this->actingAs($user)
        ->getJson(route('development.userTesting.updates', ['since' => now()->subMinute()->toIso8601String()]))
        ->assertOk()
        ->assertJsonPath('pages.0.slug', $page->slug)
        ->assertJsonPath('pages.0.tested', true)
        ->assertJsonPath('pages.0.status', 'Reviewed')
        ->assertJsonPath('pages.0.approved', false);
});

it('preserves review ownership and timestamps across repeated HTTP saves', function (): void {
    $firstActor = User::factory()->create()->assignRole(RoleName::Admin->value);
    $secondActor = User::factory()->create()->assignRole(RoleName::Admin->value);
    $page = UserTestingPage::factory()->create();

    $this->actingAs($firstActor)
        ->patch(route('development.userTesting.update', $page), [
            'tested' => '1',
            'approved' => '1',
            'roadmap_task' => '1',
            'notes' => 'Initial review',
        ])
        ->assertRedirect();

    $initial = $page->refresh()->only(['tested_by', 'tested_at', 'approved_at', 'roadmap_task_at']);
    $this->travelTo(now()->addDay());

    $this->actingAs($secondActor)
        ->patch(route('development.userTesting.update', $page), [
            'notes' => 'Updated notes',
        ])
        ->assertRedirect();

    expect($page->refresh()->only(['tested_by', 'tested_at', 'approved_at', 'roadmap_task_at']))
        ->toEqual($initial)
        ->and($page->notes)->toBe('Updated notes');
});

it('clears each user-testing state only when explicitly unchecked', function (): void {
    $actor = User::factory()->create()->assignRole(RoleName::Admin->value);
    $page = UserTestingPage::factory()->create();

    $this->actingAs($actor)
        ->patch(route('development.userTesting.update', $page), [
            'tested' => '1',
            'approved' => '1',
            'roadmap_task' => '1',
            'notes' => 'Ready for approval',
        ])
        ->assertRedirect();

    $this->actingAs($actor)
        ->patch(route('development.userTesting.update', $page), [
            'tested' => '0',
            'approved' => '0',
            'roadmap_task' => '0',
        ])
        ->assertRedirect();

    expect($page->refresh())
        ->tested_by->toBeNull()
        ->tested_at->toBeNull()
        ->approved_at->toBeNull()
        ->roadmap_task_at->toBeNull();
});

it('marks reviewed pages with explicit no-change notes as needing approval', function (): void {
    $user = User::factory()->create()->assignRole(RoleName::Admin->value);
    $page = UserTestingPage::factory()->create([
        'tested_at' => now(),
        'notes' => 'No required changes',
    ]);

    $this->actingAs($user)
        ->get(route('development.userTesting.legacy'))
        ->assertInertia(fn ($page) => $page
            ->where('pages.0.status', 'Needs Approval'));
});

it('treats reviewed pages without notes as reviewed', function (): void {
    $user = User::factory()->create()->assignRole(RoleName::Admin->value);
    UserTestingPage::factory()->create([
        'tested_at' => now(),
        'notes' => null,
    ]);

    $this->actingAs($user)
        ->get(route('development.userTesting.legacy'))
        ->assertInertia(fn ($page) => $page
            ->where('pages.0.status', 'Reviewed'));
});

it('marks completed roadmap notes as needing approval', function (): void {
    $user = User::factory()->create()->assignRole(RoleName::Admin->value);
    $page = UserTestingPage::factory()->create([
        'tested_at' => now(),
        'notes' => "FIXED VIA TASK-21\nThe detail route now renders correctly.",
    ]);

    $this->actingAs($user)
        ->get(route('development.userTesting.legacy'))
        ->assertInertia(fn ($page) => $page
            ->where('pages.0.status', 'Needs Approval'));
});

it('records when review notes are added to the roadmap without approving the page', function (): void {
    $user = User::factory()->create()->assignRole(RoleName::Admin->value);
    $page = UserTestingPage::factory()->create();

    $this->actingAs($user)
        ->patch(route('development.userTesting.update', $page), [
            'tested' => '1',
            'roadmap_task' => '1',
            'notes' => 'Track the responsive navigation issue on the roadmap.',
        ])
        ->assertRedirect(route('development.userTesting.legacy'));

    expect($page->refresh())
        ->tested_at->not->toBeNull()
        ->roadmap_task_at->not->toBeNull()
        ->approved_at->toBeNull();

    $this->actingAs($user)
        ->get(route('development.userTesting.legacy'))
        ->assertInertia(fn ($page) => $page
            ->has('pages', 1)
            ->where('pages.0.roadmap_task', true));
});

it('removes an approved page from the user testing queue', function (): void {
    $this->seed(UserTestingPageSeeder::class);
    $user = User::factory()->create()->assignRole(RoleName::Admin->value);
    $page = UserTestingPage::query()->where('slug', 'public-welcome')->firstOrFail();

    $this->actingAs($user)
        ->patch(route('development.userTesting.update', $page), [
            'tested' => '1',
            'approved' => '1',
            'notes' => 'The welcome page is ready for release.',
        ])
        ->assertRedirect(route('development.userTesting.legacy'));

    expect($page->refresh())
        ->tested_at->not->toBeNull()
        ->approved_at->not->toBeNull();

    $this->actingAs($user)
        ->get(route('development.userTesting.legacy'))
        ->assertInertia(fn ($page) => $page
            ->has('pages', 29)
            ->where('pages.0.slug', 'public-privacy'));

    $this->actingAs($user)
        ->getJson(route('development.userTesting.updates'))
        ->assertOk()
        ->assertJsonCount(29, 'pages')
        ->assertJsonPath('pages.0.slug', 'public-privacy')
        ->assertJsonMissing(['slug' => 'public-welcome']);
});

it('blocks users without the testing permission', function (): void {
    $user = User::factory()->create()->assignRole(RoleName::ClientViewer->value);

    $this->actingAs($user)
        ->get(route('development.userTesting.index'))
        ->assertForbidden();

    $this->actingAs($user)
        ->get(route('development.roadmap.index'))
        ->assertForbidden();

    $this->actingAs($user)
        ->get(route('admin.userTesting.index'))
        ->assertForbidden();

    $this->actingAs($user)
        ->get(route('admin.roadmap.index'))
        ->assertForbidden();

    $this->actingAs($user)
        ->get(route('crm.leads.index'))
        ->assertForbidden();
});

it('enforces independent roadmap and user-testing permissions', function (): void {
    $roadmapManager = User::factory()->create();
    $roadmapManager->givePermissionTo('development.roadmap.manage');
    $testingManager = User::factory()->create();
    $testingManager->givePermissionTo('development.user_testing.manage');

    $this->actingAs($roadmapManager)
        ->get(route('development.roadmap.index'))
        ->assertSuccessful();
    $this->actingAs($roadmapManager)
        ->get(route('development.userTesting.index'))
        ->assertForbidden();

    $this->actingAs($testingManager)
        ->get(route('development.userTesting.index'))
        ->assertSuccessful();
    $this->actingAs($testingManager)
        ->get(route('development.roadmap.index'))
        ->assertForbidden();
});

it('redirects legacy admin development pages to their canonical routes', function (): void {
    $user = User::factory()->create()->assignRole(RoleName::Admin->value);

    $this->actingAs($user)
        ->get(route('admin.roadmap.index'))
        ->assertRedirect(route('development.roadmap.index'));

    $this->actingAs($user)
        ->get(route('admin.userTesting.index'))
        ->assertRedirect(route('development.userTesting.index'));
});

it('shows the development roadmap to authorized users', function (): void {
    $user = User::factory()->create()->assignRole(RoleName::Admin->value);
    $planned = DevelopmentTask::factory()->planned()->create();
    $inProgress = DevelopmentTask::factory()->inProgress()->create();

    $this->actingAs($user)
        ->get(route('development.roadmap.index'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Development/Roadmap/Index')
            ->has('tasks.planned', 1)
            ->where('tasks.planned.0.id', $planned->uuid)
            ->has('tasks.in_progress', 1)
            ->where('tasks.in_progress.0.id', $inProgress->uuid)
            ->has('tasks.blocked', 0)
            ->missing('source')
            ->missing('content'));
});
