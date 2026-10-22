<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use SoutheastCode\Crm\FollowUps\Models\FollowUpTask;
use SoutheastCode\Crm\Leads\Models\Lead;
use SoutheastCode\Crm\Organizations\Models\Organization;

beforeEach(function (): void {
    app(RolePermissionSeeder::class)->run();
    $this->withoutVite();
});

test('guests are redirected to the login page', function (): void {
    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');
});

test('authenticated users can visit the dashboard', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get('/dashboard')
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Dashboard')
            ->where('canViewCrm', false)
            ->has('stats', 0));
});

test('CRM users see first-party workspace metrics and next actions', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo('crm.view');
    $organization = Organization::factory()->create();
    $lead = Lead::factory()->create([
        'organization_id' => $organization->id,
        'status' => 'qualified',
    ]);
    $task = FollowUpTask::factory()->create([
        'lead_id' => $lead->id,
        'due_at' => now()->subHour(),
    ]);

    $this->actingAs($user)->get('/dashboard')
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Dashboard')
            ->where('canViewCrm', true)
            ->has('stats', 4)
            ->where('recentLeads.0.uuid', $lead->uuid)
            ->where('recentLeads.0.organization', $organization->name)
            ->where('upcomingTasks.0.uuid', $task->uuid)
            ->where('upcomingTasks.0.is_overdue', true));
});

test('restricted CRM users only see owned and unassigned dashboard records', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo('crm.view');
    $other = User::factory()->create();
    $ownedLead = Lead::factory()->create(['owner_id' => $user->id, 'status' => 'qualified']);
    $unassignedLead = Lead::factory()->create(['owner_id' => null, 'status' => 'new']);
    $hiddenLead = Lead::factory()->create(['owner_id' => $other->id, 'status' => 'qualified']);
    $ownedTask = FollowUpTask::factory()->create(['lead_id' => $ownedLead->id, 'due_at' => now()->subHour()]);
    $unassignedTask = FollowUpTask::factory()->create(['lead_id' => $unassignedLead->id, 'due_at' => now()->addDay()]);
    $hiddenTask = FollowUpTask::factory()->create(['lead_id' => $hiddenLead->id, 'due_at' => now()->subHour()]);

    $this->actingAs($user)->get('/dashboard')
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('stats.0.value', 2)
            ->where('stats.1.value', 1)
            ->where('stats.2.value', 1)
            ->where('stats.3.value', 1)
            ->has('recentLeads', 2)
            ->where('recentLeads', fn ($leads): bool => $leads->pluck('uuid')->doesntContain($hiddenLead->uuid))
            ->has('upcomingTasks', 2)
            ->where('upcomingTasks', fn ($tasks): bool => $tasks->pluck('uuid')->doesntContain($hiddenTask->uuid))
            ->where('upcomingTasks.1.uuid', $unassignedTask->uuid));

    expect($hiddenTask->refresh()->lead->isVisibleTo($user))->toBeFalse();

    $admin = User::factory()->create()->assignRole('Admin');

    $this->actingAs($admin)->get('/dashboard')
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('stats.0.value', 3)
            ->where('stats.1.value', 2)
            ->where('stats.2.value', 2)
            ->where('stats.3.value', 1)
            ->has('recentLeads', 3)
            ->has('upcomingTasks', 3));
});
