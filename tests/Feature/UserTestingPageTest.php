<?php

declare(strict_types=1);

use App\Enums\RoleName;
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

it('saves a review and exposes it through the updates endpoint', function (): void {
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
        ->tested_at->not->toBeNull();

    $this->actingAs($user)
        ->getJson(route('admin.userTesting.updates', ['since' => now()->subMinute()->toIso8601String()]))
        ->assertOk()
        ->assertJsonPath('pages.0.slug', $page->slug)
        ->assertJsonPath('pages.0.tested', true);
});

it('blocks users without the testing permission', function (): void {
    $user = User::factory()->create()->assignRole(RoleName::ClientViewer->value);

    $this->actingAs($user)
        ->get(route('admin.userTesting.index'))
        ->assertForbidden();
});
