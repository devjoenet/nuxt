<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    $this->withoutVite();

    config([
        'inertia.ssr.enabled' => true,
        'inertia.ssr.ensure_bundle_exists' => false,
    ]);

    Http::fake([
        'http://127.0.0.1:13714/render' => Http::response([
            'head' => ['<meta name="ssr-marker" content="public">'],
            'body' => '<div id="app" data-server-rendered="true">Server-rendered page</div>',
        ]),
    ]);
});

test('public marketing routes use server-side rendering', function (string $routeName): void {
    $this->get(route($routeName))
        ->assertOk()
        ->assertSee('data-server-rendered="true"', false)
        ->assertSee('name="ssr-marker"', false);

    Http::assertSentCount(1);
})->with([
    'home' => 'home',
    'privacy' => 'privacy',
]);

test('authentication and workspace routes skip server-side rendering', function (): void {
    $this->get(route('login'))
        ->assertOk()
        ->assertDontSee('data-server-rendered="true"', false);

    app(RolePermissionSeeder::class)->run();

    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('data-server-rendered="true"', false);

    Http::assertNothingSent();
});
