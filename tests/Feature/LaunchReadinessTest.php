<?php

declare(strict_types=1);

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->withoutVite();
});

test('public launch surfaces remain available without authentication', function (): void {
    $this->get(route('home'))->assertOk();
    $this->get(route('privacy'))->assertOk();
    $this->get('/inbox')->assertNotFound();
});

test('authenticated launch surfaces have useful first-party page payloads', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page->component('Dashboard')->has('canViewCrm'));
});

test('protected workspace routes reject guests', function (): void {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
    $this->get(route('crm.leads.index'))->assertRedirect(route('login'));
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));

    $this->get(route('profile.edit'))->assertRedirect(route('login'));
});

test('inertia failures render the application error page', function (): void {
    $this->withHeaders([
        'X-Inertia' => 'true',
        'X-Requested-With' => 'XMLHttpRequest',
    ])->get('/missing-page')
        ->assertNotFound()
        ->assertJsonPath('component', 'ErrorPage')
        ->assertJsonPath('props.status', 404);
});

test('login attempts are rate limited by email and IP address', function (): void {
    User::factory()->create(['email' => 'rate-limit@example.com']);

    foreach (range(1, 5) as $attempt) {
        $this->post(route('login'), [
            'email' => 'rate-limit@example.com',
            'password' => 'incorrect-password',
        ])->assertSessionHasErrors('email');
    }

    $this->post(route('login'), [
        'email' => 'rate-limit@example.com',
        'password' => 'incorrect-password',
    ])->assertSessionHasErrors('email');
});
