<?php

declare(strict_types=1);

use App\Actions\UpdateAdminUserAccess;
use App\Actions\UpdateBusinessSetting;
use App\Data\AdminUserAccessData;
use App\Data\BusinessSettingData;
use App\Enums\RoleName;
use App\Models\BusinessSetting;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Exceptions\RoleDoesNotExist;
use Spatie\Permission\Models\Role;

test('admin user access action enforces super-admin and self-deactivation invariants outside HTTP', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findOrCreate(RoleName::Admin->value));
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(Role::findOrCreate(RoleName::SuperAdmin->value));
    $action = app(UpdateAdminUserAccess::class);

    expect(fn (): User => $action->handle($admin, new AdminUserAccessData(RoleName::Admin->value, false), $admin))
        ->toThrow(ValidationException::class);
    expect(fn (): User => $action->handle($superAdmin, new AdminUserAccessData(RoleName::Admin->value, true), $admin))
        ->toThrow(ValidationException::class);
    expect(fn (): User => $action->handle($admin, new AdminUserAccessData(RoleName::SuperAdmin->value, true), $admin))
        ->toThrow(ValidationException::class);

    expect($admin->refresh()->is_active)->toBeTrue()
        ->and($superAdmin->refresh()->hasRole(RoleName::SuperAdmin->value))->toBeTrue();
});

test('admin user access action rolls back user changes when role synchronization fails', function (): void {
    $actor = User::factory()->create();
    $actor->assignRole(Role::findOrCreate(RoleName::SuperAdmin->value));
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate(RoleName::SalesOps->value));

    expect(fn (): User => app(UpdateAdminUserAccess::class)->handle(
        $user,
        new AdminUserAccessData('Missing role', false),
        $actor,
    ))->toThrow(RoleDoesNotExist::class);

    expect($user->refresh()->is_active)->toBeTrue()
        ->and($user->deactivated_at)->toBeNull()
        ->and($user->hasRole(RoleName::SalesOps->value))->toBeTrue()
        ->and(Activity::query()->where('description', 'User access updated.')->count())->toBe(0);
});

test('business setting action records only changed field names with its explicit actor', function (): void {
    $actor = User::factory()->create();
    BusinessSetting::factory()->create([
        'company_name' => 'Original name',
        'notification_email' => 'original@example.com',
        'default_task_due_days' => 3,
    ]);
    $data = new BusinessSettingData('Updated name', 'updated@example.com', 5);

    $setting = app(UpdateBusinessSetting::class)->handle($data, $actor);
    app(UpdateBusinessSetting::class)->handle($data, $actor);

    $activity = Activity::query()->where('description', 'Business settings updated.')->sole();

    expect($setting->company_name)->toBe('Updated name')
        ->and($setting->default_task_due_days)->toBe(5)
        ->and($activity->causer_id)->toBe($actor->id)
        ->and($activity->properties->get('changed_fields'))->toEqualCanonicalizing(['company_name', 'notification_email', 'default_task_due_days'])
        ->and($activity->properties->toArray())->not->toHaveKey('notification_email_value');
});
