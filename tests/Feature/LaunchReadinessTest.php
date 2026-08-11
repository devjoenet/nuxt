<?php

declare(strict_types=1);

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->withoutVite();
});

test('public launch surfaces remain available without authentication', function (): void {
    $this->get(route('home'))->assertOk();
    $this->get(route('privacy'))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Privacy')
            ->where('canonicalUrl', config('app.url').'/privacy'));
    expect(app('router')->getRoutes()->getByName('privacy')->uri())->toBe('privacy');
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

test('login and two factor mutation routes expose throttling middleware', function (): void {
    expect(app('router')->getRoutes()->getByName('login.store')->middleware())
        ->toContain('throttle:login-route')
        ->and(app('router')->getRoutes()->getByName('two-factor.login.store')->middleware())
        ->toContain('throttle:two-factor')
        ->and(app('router')->getRoutes()->getByName('two-factor.enable')->middleware())
        ->toContain('throttle:two-factor-management')
        ->and(app('router')->getRoutes()->getByName('two-factor.disable')->middleware())
        ->toContain('throttle:two-factor-management');
});
