<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureHorizonAccess;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Symfony\Component\HttpFoundation\Response;

test('guest cannot view horizon', function (): void {
    expect(Gate::check('viewHorizon'))->toBeFalse();

    $this->get('/horizon')->assertForbidden();
});

test('authenticated user cannot view horizon without permission', function (): void {
    $user = User::factory()->create([
        'email' => 'user@example.com',
    ]);

    $canView = Gate::forUser($user)->check('viewHorizon');

    expect($canView)->toBeFalse();

    $this->actingAs($user)->get('/horizon')->assertForbidden();
});

test('authenticated user with jobs manage permission can view horizon', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::findOrCreate('jobs.manage'));

    $canView = Gate::forUser($user)->check('viewHorizon');

    expect($canView)->toBeTrue();

    $this->actingAs($user)->get('/horizon')->assertSuccessful();
});

test('authenticated user with jobs view permission can inspect horizon but cannot mutate it', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::findOrCreate('jobs.view'));

    expect(Gate::forUser($user)->check('viewHorizon'))->toBeTrue();

    $this->actingAs($user)->get('/horizon')->assertSuccessful();
    $this->actingAs($user)->post('/horizon/api/jobs/retry/failed-job-id')->assertForbidden();
});

test('jobs manage mutations are recorded in the audit log', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::findOrCreate('jobs.manage'));
    $request = Request::create('/horizon/api/jobs/retry/failed-job-id', 'POST');
    $request->setUserResolver(fn (): User => $user);

    $response = (new EnsureHorizonAccess)->handle($request, fn (Request $request): Response => response('ok'));

    expect($response->isSuccessful())->toBeTrue();

    $activity = Activity::query()->where('description', 'Horizon action executed.')->sole();

    expect($activity->causer?->is($user))->toBeTrue()
        ->and($activity->properties->toArray())->toMatchArray([
            'method' => 'POST',
            'path' => 'horizon/api/jobs/retry/failed-job-id',
        ]);
});
