<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    $this->withoutVite();

    config([
        'inertia.ssr.enabled' => true,
        'inertia.ssr.ensure_bundle_exists' => false,
        'inertia.ssr.hot_url' => 'http://127.0.0.1:13714',
    ]);

    Http::fake([
        'http://127.0.0.1:13714/*' => Http::response([
            'head' => ['<meta name="ssr-marker" content="public">'],
            'body' => '<div id="app" data-server-rendered="true">Server-rendered page</div>',
        ]),
    ]);
});

test('Inertia uses the version three page discovery configuration', function (): void {
    expect(config('inertia.pages'))
        ->toBe([
            'ensure_pages_exist' => false,
            'paths' => [resource_path('js/pages')],
            'extensions' => ['js', 'jsx', 'svelte', 'ts', 'tsx', 'vue'],
        ])
        ->and(config('inertia.testing'))
        ->toBe(['ensure_pages_exist' => true]);
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

test('the appearance preference is included in server-rendered page props', function (): void {
    $this->withUnencryptedCookie('appearance', 'dark')
        ->get(route('home'))
        ->assertOk();

    Http::assertSent(function (Request $request): bool {
        return data_get($request->data(), 'props.appearance') === 'dark';
    });
});

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
