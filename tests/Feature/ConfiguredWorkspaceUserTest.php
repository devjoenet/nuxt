<?php

declare(strict_types=1);

use App\Enums\RoleName;
use App\Support\WorkspaceUsers;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Activitylog\Models\Activity;
use Tests\Fixtures\AlternateWorkspaceUser;

test('configured user route binding preserves the target model and actor on access updates', function (): void {
    config(['auth.providers.users.model' => AlternateWorkspaceUser::class]);
    app(RolePermissionSeeder::class)->run();
    $actor = WorkspaceUsers::model()::factory()->create()->assignRole(RoleName::Admin->value);
    $target = WorkspaceUsers::model()::factory()->create()->assignRole(RoleName::SalesOps->value);

    $this->actingAs($actor)->patch(route('admin.users.update', $target), [
        'role' => RoleName::ClientViewer->value,
        'is_active' => false,
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect($target->refresh()->is_active)->toBeFalse();
    $activity = Activity::query()->where('description', 'User access updated.')->sole();
    expect($activity->subject_type)->toBe(AlternateWorkspaceUser::class)
        ->and($activity->subject_id)->toBe($target->id)
        ->and($activity->causer_type)->toBe(AlternateWorkspaceUser::class)
        ->and($activity->causer_id)->toBe($actor->id);
});

test('inactive configured workspace users cannot log in', function (): void {
    config(['auth.providers.users.model' => AlternateWorkspaceUser::class]);
    $user = WorkspaceUsers::model()::factory()->create(['is_active' => false]);

    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
        ->assertSessionHasErrors('email');
    $this->assertGuest();
});
