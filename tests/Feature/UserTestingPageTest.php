<?php

declare(strict_types=1);

use App\Enums\RoleName;
use App\Models\Contact;
use App\Models\Lead;
use App\Models\Organization;
use App\Models\User;
use App\Models\UserTestingPage;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\UserTestingPageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

it('shows the seeded review checklist to authorized users', function (): void {
    $this->seed(UserTestingPageSeeder::class);
    $user = User::factory()->create()->assignRole(RoleName::Admin->value);

    $this->actingAs($user)
        ->get(route('admin.userTesting.index'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->component('Admin/UserTesting/Index')->has('pages', 30));
});

it('resolves database-backed checklist links to the first visible records', function (): void {
    $this->seed(UserTestingPageSeeder::class);
    $user = User::factory()->create()->assignRole(RoleName::Admin->value);
    $organization = Organization::factory()->create();
    $contact = Contact::factory()->for($organization)->create();
    $lead = Lead::factory()->for($contact)->create(['organization_id' => $organization->getKey()]);

    $this->actingAs($user)
        ->get(route('admin.userTesting.index'))
        ->assertInertia(fn ($page) => $page
            ->where('pages.0.url', route('home', [], false))
            ->where('pages.0.roadmap_task_number', 14)
            ->where('pages.0.status', 'In Development')
            ->where('pages.1.url', route('privacy', [], false))
            ->where('pages.1.roadmap_task_number', 15)
            ->where('pages.11.url', route('crm.leads.show', $lead, false))
            ->where('pages.11.roadmap_task_number', 22)
            ->where('pages.11.status', 'In Development')
            ->where('pages.12.url', route('crm.leads.edit', $lead, false))
            ->where('pages.15.url', route('crm.organizations.show', $organization, false))
            ->where('pages.15.roadmap_task_number', 21)
            ->where('pages.15.status', 'Needs verification')
            ->where('pages.19.url', route('crm.contacts.show', $contact, false))
            ->where('pages.19.roadmap_task_number', 21)
            ->where('pages.19.status', 'Needs verification')
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
        ->patch(route('admin.userTesting.update', $page), [
            'tested' => '1',
            'notes' => 'Check the mobile navigation spacing before release.',
        ])
        ->assertRedirect(route('admin.userTesting.index'));

    expect($page->refresh())
        ->tested_by->toBe($user->getKey())
        ->notes->toBe('Check the mobile navigation spacing before release.')
        ->tested_at->not->toBeNull()
        ->approved_at->toBeNull()
        ->roadmap_task_at->toBeNull();

    $this->actingAs($user)
        ->getJson(route('admin.userTesting.updates', ['since' => now()->subMinute()->toIso8601String()]))
        ->assertOk()
        ->assertJsonPath('pages.0.slug', $page->slug)
        ->assertJsonPath('pages.0.tested', true)
        ->assertJsonPath('pages.0.status', 'Reviewed')
        ->assertJsonPath('pages.0.approved', false);
});

it('marks reviewed pages with explicit no-change notes as needing approval', function (): void {
    $user = User::factory()->create()->assignRole(RoleName::Admin->value);
    $page = UserTestingPage::factory()->create([
        'tested_at' => now(),
        'notes' => 'No required changes',
    ]);

    $this->actingAs($user)
        ->get(route('admin.userTesting.index'))
        ->assertInertia(fn ($page) => $page
            ->where('pages.0.status', 'Needs Approval'));
});

it('marks completed roadmap notes as needing approval', function (): void {
    $user = User::factory()->create()->assignRole(RoleName::Admin->value);
    $page = UserTestingPage::factory()->create([
        'tested_at' => now(),
        'notes' => "FIXED VIA TASK-21\nThe detail route now renders correctly.",
    ]);

    $this->actingAs($user)
        ->get(route('admin.userTesting.index'))
        ->assertInertia(fn ($page) => $page
            ->where('pages.0.status', 'Needs Approval'));
});

it('records when review notes are added to the roadmap without approving the page', function (): void {
    $user = User::factory()->create()->assignRole(RoleName::Admin->value);
    $page = UserTestingPage::factory()->create();

    $this->actingAs($user)
        ->patch(route('admin.userTesting.update', $page), [
            'tested' => '1',
            'roadmap_task' => '1',
            'notes' => 'Track the responsive navigation issue on the roadmap.',
        ])
        ->assertRedirect(route('admin.userTesting.index'));

    expect($page->refresh())
        ->tested_at->not->toBeNull()
        ->roadmap_task_at->not->toBeNull()
        ->approved_at->toBeNull();

    $this->actingAs($user)
        ->get(route('admin.userTesting.index'))
        ->assertInertia(fn ($page) => $page
            ->has('pages', 1)
            ->where('pages.0.roadmap_task', true));
});

it('removes an approved page from the user testing queue', function (): void {
    $this->seed(UserTestingPageSeeder::class);
    $user = User::factory()->create()->assignRole(RoleName::Admin->value);
    $page = UserTestingPage::query()->where('slug', 'public-welcome')->firstOrFail();

    $this->actingAs($user)
        ->patch(route('admin.userTesting.update', $page), [
            'tested' => '1',
            'approved' => '1',
            'notes' => 'The welcome page is ready for release.',
        ])
        ->assertRedirect(route('admin.userTesting.index'));

    expect($page->refresh())
        ->tested_at->not->toBeNull()
        ->approved_at->not->toBeNull();

    $this->actingAs($user)
        ->get(route('admin.userTesting.index'))
        ->assertInertia(fn ($page) => $page
            ->has('pages', 29)
            ->where('pages.0.slug', 'public-privacy'));

    $this->actingAs($user)
        ->getJson(route('admin.userTesting.updates'))
        ->assertOk()
        ->assertJsonCount(29, 'pages')
        ->assertJsonPath('pages.0.slug', 'public-privacy')
        ->assertJsonMissing(['slug' => 'public-welcome']);
});

it('blocks users without the testing permission', function (): void {
    $user = User::factory()->create()->assignRole(RoleName::ClientViewer->value);

    $this->actingAs($user)
        ->get(route('admin.userTesting.index'))
        ->assertForbidden();

    $this->actingAs($user)
        ->get(route('admin.roadmap.index'))
        ->assertForbidden();
});

it('shows the development roadmap to authorized users', function (): void {
    $user = User::factory()->create()->assignRole(RoleName::Admin->value);

    $this->actingAs($user)
        ->get(route('admin.roadmap.index'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Roadmap/Index')
            ->where('source', 'docs/roadmap.md')
            ->where('content', fn ($content): bool => str_contains((string) $content, 'Southeast Code Roadmap')));
});
