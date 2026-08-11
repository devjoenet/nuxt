<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Route;

function currentTask16InertiaVersion(): string
{
    $manifest = public_path('build/manifest.json');

    return file_exists($manifest) ? hash_file('xxh128', $manifest) : '';
}

beforeEach(function (): void {
    $this->withoutVite();

    Route::middleware('web')->get('/__task-16/errors/{status}', function (int $status): never {
        abort($status);
    })->whereNumber('status');

    Route::middleware('web')->get('/__task-16/sensitive-error', function (): never {
        throw new RuntimeException('sensitive exception details must never reach the response');
    });

    Route::middleware('web')->get('/__task-16/unsupported-error', function (): Illuminate\Http\JsonResponse {
        return response()->json(['default' => true], 400);
    });
});

dataset('supported error statuses', [403, 404, 419, 429, 500, 503]);

test('document requests preserve supported status codes and render the error page', function (int $status): void {
    $response = $this->get('/__task-16/errors/'.$status);

    $response->assertStatus($status)->assertSee('ErrorPage')->assertSee((string) $status);
})->with('supported error statuses');

test('inertia requests preserve supported status codes and expose only the status prop', function (int $status): void {
    $response = $this->withHeaders([
        'X-Inertia' => 'true',
        'X-Requested-With' => 'XMLHttpRequest',
        'X-Inertia-Version' => currentTask16InertiaVersion(),
    ])->get('/__task-16/errors/'.$status);

    $response
        ->assertStatus($status)
        ->assertJsonPath('component', 'ErrorPage')
        ->assertJsonPath('props.status', $status)
        ->assertJsonPath('props.auth.user', null);
})->with('supported error statuses');

test('shared authentication data is available to authenticated error pages', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withHeaders(['X-Inertia' => 'true', 'X-Requested-With' => 'XMLHttpRequest', 'X-Inertia-Version' => currentTask16InertiaVersion()])
        ->get('/__task-16/errors/403')
        ->assertForbidden()
        ->assertJsonPath('component', 'ErrorPage')
        ->assertJsonPath('props.auth.user.id', $user->uuid);
});

test('exception details are not exposed in document or inertia responses', function (): void {
    $this->get('/__task-16/sensitive-error')
        ->assertStatus(500)
        ->assertDontSee('sensitive exception details must never reach the response');

    $this->withHeaders(['X-Inertia' => 'true', 'X-Requested-With' => 'XMLHttpRequest', 'X-Inertia-Version' => currentTask16InertiaVersion()])
        ->get('/__task-16/sensitive-error')
        ->assertServerError()
        ->assertJsonPath('component', 'ErrorPage')
        ->assertJsonPath('props.status', 500)
        ->assertJsonMissing(['sensitive exception details must never reach the response']);
});

test('unsupported statuses use Laravel default exception handling', function (): void {
    $this->withHeaders([
        'X-Inertia' => 'true',
        'X-Requested-With' => 'XMLHttpRequest',
        'X-Inertia-Version' => currentTask16InertiaVersion(),
    ])->get('/__task-16/unsupported-error')
        ->assertBadRequest()
        ->assertJsonMissingPath('component');
});
