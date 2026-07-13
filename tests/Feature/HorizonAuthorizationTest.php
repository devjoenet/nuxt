<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;

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
