<?php

declare(strict_types=1);

use App\Models\FollowUpTask;
use App\Models\Lead;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Inertia\Testing\AssertableInertia as Assert;

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
