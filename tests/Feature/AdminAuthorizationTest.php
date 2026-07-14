<?php

declare(strict_types=1);

use App\Enums\RoleName;
use App\Models\BusinessSetting;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    app(RolePermissionSeeder::class)->run();
    $this->withoutVite();
});

function adminUser(string $role = 'Admin'): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

test('users without admin access cannot enter the admin workspace', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
    $this->actingAs($user)->get(route('admin.users.index'))->assertForbidden();
});

test('inactive users cannot log in or use an existing session', function (): void {
    $user = User::factory()->create(['is_active' => false]);

    $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
    $this->actingAs($user)->get('/dashboard')->assertRedirect('/login');
});

test('admins can view the dashboard and protected admin screens', function (): void {
    $user = adminUser();

    $this->actingAs($user)->get(route('admin.dashboard'))->assertOk()->assertInertia(fn (Assert $page): Assert => $page->component('Admin/Dashboard'));
    $this->actingAs($user)->get(route('admin.users.index'))->assertOk()->assertInertia(fn (Assert $page): Assert => $page->component('Admin/Users/Index'));
    $this->actingAs($user)->get(route('admin.roles.index'))->assertOk();
    $this->actingAs($user)->get(route('admin.audit.index'))->assertOk();
});

test('admins can invite users with a role and a password setup notification', function (): void {
    Notification::fake();
    $admin = adminUser();

    $this->actingAs($admin)->post(route('admin.users.store'), [
        'name' => 'New Operator',
        'email' => 'operator@example.com',
        'role' => RoleName::SalesOps->value,
    ])->assertRedirect(route('admin.users.index'));

    $invited = User::query()->where('email', 'operator@example.com')->sole();

    expect($invited->hasRole(RoleName::SalesOps->value))->toBeTrue();
    Notification::assertSentTo($invited, Illuminate\Auth\Notifications\ResetPassword::class);
});

test('admins can update access and the change is auditable', function (): void {
    $admin = adminUser();
    $user = User::factory()->create();
    $user->assignRole(RoleName::SalesOps->value);

    $this->actingAs($admin)->patch(route('admin.users.update', $user), [
        'role' => RoleName::ClientViewer->value,
        'is_active' => false,
    ])->assertRedirect();

    expect($user->refresh()->is_active)->toBeFalse()
        ->and($user->hasRole(RoleName::ClientViewer->value))->toBeTrue();
    expect(Activity::query()->where('description', 'User access updated.')->exists())->toBeTrue();
});

test('admins can resend verification and reset two-factor authentication', function (): void {
    Notification::fake();
    $admin = adminUser();
    $user = User::factory()->unverified()->withTwoFactorAuthentication()->create();

    $this->actingAs($admin)->post(route('admin.users.resendVerification', $user))->assertRedirect();
    $this->actingAs($admin)->post(route('admin.users.resetTwoFactor', $user))->assertRedirect();

    expect($user->refresh()->two_factor_secret)->toBeNull()
        ->and($user->two_factor_recovery_codes)->toBeNull()
        ->and($user->two_factor_confirmed_at)->toBeNull();
    Notification::assertSentTo($user, Illuminate\Auth\Notifications\VerifyEmail::class);
});

test('admins cannot deactivate themselves or demote a super admin', function (): void {
    $admin = adminUser();

    $this->actingAs($admin)->patch(route('admin.users.update', $admin), [
        'role' => RoleName::Admin->value,
        'is_active' => false,
    ])->assertSessionHasErrors('is_active');

    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(RoleName::SuperAdmin->value);

    $this->actingAs($admin)->patch(route('admin.users.update', $superAdmin), [
        'role' => RoleName::Admin->value,
        'is_active' => true,
    ])->assertSessionHasErrors('role');
});

test('admins can update business settings', function (): void {
    $admin = adminUser();

    $this->actingAs($admin)->patch(route('admin.settings.update'), [
        'company_name' => 'Southeast Code LLC',
        'notification_email' => 'ops@example.com',
        'default_task_due_days' => 5,
    ])->assertRedirect();

    expect(BusinessSetting::current()->company_name)->toBe('Southeast Code LLC')
        ->and(BusinessSetting::current()->default_task_due_days)->toBe(5);
});

test('super admins bypass permission checks while protected role permissions remain immutable', function (): void {
    $superAdmin = adminUser(RoleName::SuperAdmin->value);
    $role = Role::findByName(RoleName::Admin->value);

    $this->actingAs($superAdmin)->get(route('admin.audit.index'))->assertOk();
    $this->actingAs($superAdmin)->patch(route('admin.roles.update', $role), [
        'permission_ids' => Permission::query()->pluck('id')->all(),
    ])->assertRedirect();

    $this->actingAs($superAdmin)->patch(route('admin.roles.update', Role::findByName(RoleName::SuperAdmin->value)), [
        'permission_ids' => [],
    ])->assertSessionHasErrors('permission_ids');
});
