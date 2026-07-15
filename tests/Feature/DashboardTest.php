<?php

declare(strict_types=1);

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

    $this->actingAs($user)->get('/dashboard')
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Dashboard')
            ->where('canViewCrm', true)
            ->has('stats', 4)
            ->has('recentLeads')
            ->has('upcomingTasks'));
});
