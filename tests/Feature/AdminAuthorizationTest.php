<?php

declare(strict_types=1);

use App\Actions\CreateRole;
use App\Actions\UpdateRolePermissions;
use App\Data\CreateRoleData;
use App\Data\RolePermissionsData;
use App\Enums\Permission as PermissionName;
use App\Enums\PermissionGroup;
use App\Enums\RoleName;
use App\Models\BusinessSetting;
use App\Models\Contact;
use App\Models\CrmActivity;
use App\Models\Lead;
use App\Models\Note;
use App\Models\User;
use App\Notifications\UserInvitationNotification;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

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

test('users without audit or settings permissions cannot open those screens', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('admin.audit.index'))->assertForbidden();
    $this->actingAs($user)->get(route('admin.settings.edit'))->assertForbidden();
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

test('role management exposes the role and permission payload for a focused editor', function (): void {
    $admin = adminUser();

    $this->actingAs($admin)
        ->get(route('admin.roles.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Admin/Roles/Index')
            ->where('roles', fn (mixed $roles): bool => collect($roles)->contains(fn (mixed $role): bool => data_get($role, 'name') === RoleName::Admin->value
                && data_get($role, 'protected') === false
                && is_array(data_get($role, 'permission_ids'))))
            ->where('roles', fn (mixed $roles): bool => collect($roles)->contains(fn (mixed $role): bool => data_get($role, 'name') === RoleName::SuperAdmin->value
                && data_get($role, 'protected') === true))
            ->where('permissionGroups', fn (mixed $groups): bool => collect($groups)->contains(fn (mixed $group): bool => data_get($group, 'key') === PermissionGroup::Admin->value
                && data_get($group, 'label') === PermissionGroup::Admin->label()
                && collect(data_get($group, 'permissions'))->contains(fn (mixed $permission): bool => data_get($permission, 'name') === 'admin.roles.manage'
                    && data_get($permission, 'label') === 'Roles Manage'))));
});

test('role permission catalog groups web permissions and preserves uncatalogued entries', function (): void {
    $admin = adminUser();
    $uncataloguedPermission = Permission::findOrCreate('reports.exports.create', 'web');
    $apiPermission = Permission::findOrCreate('reports.exports.create', 'api');

    $this->actingAs($admin)
        ->get(route('admin.roles.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('permissionGroups', function (mixed $groups) use ($apiPermission, $uncataloguedPermission): bool {
                $permissionGroups = collect($groups);
                $permissions = $permissionGroups->flatMap(fn (mixed $group): array => data_get($group, 'permissions', []));
                $adminGroup = $permissionGroups->firstWhere('key', PermissionGroup::Admin->value);
                $otherGroup = $permissionGroups->firstWhere('key', PermissionGroup::Other->value);

                return $permissionGroups->pluck('key')->all() === collect(PermissionGroup::cases())->pluck('value')->all()
                    && collect(data_get($adminGroup, 'permissions'))->pluck('name')->all() === [
                        PermissionName::AdminDashboardView->value,
                        PermissionName::AdminUsersManage->value,
                        PermissionName::AdminRolesManage->value,
                    ]
                    && data_get($otherGroup, 'label') === PermissionGroup::Other->label()
                    && collect(data_get($otherGroup, 'permissions'))->contains(fn (mixed $permission): bool => data_get($permission, 'id') === $uncataloguedPermission->getKey()
                        && data_get($permission, 'label') === 'Reports Exports Create')
                    && $permissions->contains(fn (mixed $permission): bool => data_get($permission, 'id') === $uncataloguedPermission->getKey())
                    && ! $permissions->contains(fn (mixed $permission): bool => data_get($permission, 'id') === $apiPermission->getKey());
            }));
});

test('admins can replace and clear ordinary role permissions with an audit record', function (): void {
    $admin = adminUser();
    $role = Role::findByName(RoleName::SalesOps->value);
    $permissionIds = Permission::query()->where('guard_name', 'web')->orderBy('id')->limit(2)->pluck('id')->all();

    $this->actingAs($admin)
        ->patch(route('admin.roles.update', $role), ['permission_ids' => $permissionIds])
        ->assertRedirect();

    expect($role->refresh()->permissions->pluck('id')->sort()->values()->all())->toBe(collect($permissionIds)->sort()->values()->all());

    $activity = Activity::query()->where('description', 'Role permissions updated.')->latest('id')->sole();

    expect($activity->properties->get('permission_ids'))->toBe($permissionIds);

    $this->actingAs($admin)
        ->patch(route('admin.roles.update', $role), ['permission_ids' => []])
        ->assertRedirect();

    expect($role->refresh()->permissions)->toBeEmpty();
});

test('admins can create a role with permissions and an attributed audit record', function (): void {
    $admin = adminUser();
    $permissionIds = Permission::query()->where('guard_name', 'web')->orderBy('id')->limit(2)->pluck('id')->all();

    $this->actingAs($admin)
        ->post(route('admin.roles.store'), [
            'name' => '  Project   Manager  ',
            'permission_ids' => $permissionIds,
        ])
        ->assertRedirect(route('admin.roles.index'))
        ->assertSessionHas('success', 'Role created.');

    $role = Role::findByName('Project Manager');

    expect($role->guard_name)->toBe('web')
        ->and($role->permissions->pluck('id')->sort()->values()->all())->toBe(collect($permissionIds)->sort()->values()->all());

    $activity = Activity::query()->where('description', 'Role created.')->latest('id')->sole();

    expect($activity->causer?->is($admin))->toBeTrue()
        ->and($activity->subject?->is($role))->toBeTrue()
        ->and($activity->properties->get('permission_ids'))->toBe($permissionIds);
});

test('role creation rejects duplicate names and invalid permission ids', function (): void {
    $admin = adminUser();
    $existingRole = Role::findByName(RoleName::Admin->value);
    $webPermission = Permission::query()->where('guard_name', 'web')->firstOrFail();
    $apiPermission = Permission::findOrCreate('api-role-create-only', 'api');

    $this->actingAs($admin)
        ->post(route('admin.roles.store'), ['name' => $existingRole->name])
        ->assertSessionHasErrors('name');

    foreach ([
        [$webPermission->getKey(), $webPermission->getKey()],
        [$apiPermission->getKey()],
        [99999999],
    ] as $permissionIds) {
        $this->actingAs($admin)
            ->post(route('admin.roles.store'), [
                'name' => 'Rejected Role',
                'permission_ids' => $permissionIds,
            ])
            ->assertSessionHasErrors('permission_ids.0');
    }

    expect(Role::query()->where('name', 'Rejected Role')->exists())->toBeFalse();
});

test('users without role management cannot create roles', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('admin.roles.store'), ['name' => 'Unauthorized Role'])
        ->assertForbidden();

    expect(Role::query()->where('name', 'Unauthorized Role')->exists())->toBeFalse();
});

test('create role action rejects invalid permission ids outside the HTTP boundary', function (): void {
    $admin = adminUser();

    expect(fn (): Role => app(CreateRole::class)->handle(
        new CreateRoleData(name: 'Invalid Action Role', permissionIds: [99999999]),
        $admin,
    ))->toThrow(ValidationException::class);

    expect(Role::query()->where('name', 'Invalid Action Role')->exists())->toBeFalse();
});

test('role permission updates reject duplicate, missing, and other-guard permission ids', function (): void {
    $admin = adminUser();
    $role = Role::findByName(RoleName::SalesOps->value);
    $webPermission = Permission::query()->where('guard_name', 'web')->firstOrFail();
    $apiPermission = Permission::findOrCreate('api-only', 'api');

    $role->syncPermissions([$webPermission]);

    foreach ([
        ['permission_ids' => [$webPermission->getKey(), $webPermission->getKey()]],
        ['permission_ids' => [$apiPermission->getKey()]],
        ['permission_ids' => [99999999]],
    ] as $payload) {
        $this->actingAs($admin)
            ->patch(route('admin.roles.update', $role), $payload)
            ->assertSessionHasErrors('permission_ids.0');
    }

    expect($role->refresh()->permissions->pluck('id')->all())->toBe([$webPermission->getKey()]);
});

test('users without role management cannot view or update roles', function (): void {
    $user = User::factory()->create();
    $role = Role::findByName(RoleName::Admin->value);

    $this->actingAs($user)->get(route('admin.roles.index'))->assertForbidden();
    $this->actingAs($user)->patch(route('admin.roles.update', $role), ['permission_ids' => []])->assertForbidden();
});

test('admins can invite users with a role and a queued onboarding notification', function (): void {
    Notification::fake();
    $admin = adminUser();

    $this->actingAs($admin)->post(route('admin.users.store'), [
        'name' => 'New Operator',
        'email' => 'operator@example.com',
        'role' => RoleName::SalesOps->value,
    ])->assertRedirect(route('admin.users.index'));

    $invited = User::query()->where('email', 'operator@example.com')->sole();

    expect($invited->hasRole(RoleName::SalesOps->value))->toBeTrue();

    /** @var UserInvitationNotification $notification */
    $notification = Notification::sent($invited, UserInvitationNotification::class)->sole();
    $mail = $notification->toMail($invited);

    expect($notification)->toBeInstanceOf(ShouldQueue::class)
        ->and($mail->subject)->toBe('Set up your Southeast Code account')
        ->and($mail->actionText)->toBe('Set up your account')
        ->and($mail->actionUrl)->toBe(route('password.reset', [
            'token' => $notification->token,
            'email' => $invited->email,
        ]))
        ->and(implode(' ', $mail->introLines))->toContain('administrator created')
        ->and(implode(' ', $mail->introLines))->toContain('Choose your password')
        ->and(implode(' ', $mail->introLines))->toContain('verify your email address');

    auth()->logout();
    $this->get($mail->actionUrl)->assertOk();

    expect(Activity::query()->where('description', 'User invitation sent.')->exists())->toBeTrue();
    Notification::assertNotSentTo($invited, ResetPassword::class);
});

test('admin invitations enforce role and duplicate-email boundaries', function (): void {
    $admin = adminUser();
    $existing = User::factory()->create(['email' => 'existing@example.com']);

    $this->actingAs($admin)->post(route('admin.users.store'), [
        'name' => 'Blocked Super Admin',
        'email' => 'blocked@example.com',
        'role' => RoleName::SuperAdmin->value,
    ])->assertSessionHasErrors('role');

    $this->actingAs($admin)->post(route('admin.users.store'), [
        'name' => 'Duplicate User',
        'email' => $existing->email,
        'role' => RoleName::SalesOps->value,
    ])->assertSessionHasErrors('email');
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
    $admin = adminUser();
    $user = User::factory()->unverified()->withTwoFactorAuthentication()->create();
    Notification::fake();

    $this->actingAs($admin)->post(route('admin.users.resendVerification', $user))->assertRedirect();
    $this->actingAs($admin)->post(route('admin.users.resetTwoFactor', $user))->assertRedirect();

    expect($user->refresh()->two_factor_secret)->toBeNull()
        ->and($user->two_factor_recovery_codes)->toBeNull()
        ->and($user->two_factor_confirmed_at)->toBeNull();
    Notification::assertSentTo($user, VerifyEmail::class);
    expect(Activity::query()->where('description', 'Verification email resent.')->exists())->toBeTrue()
        ->and(Activity::query()->where('description', 'Two-factor authentication reset.')->exists())->toBeTrue();
});

test('admin user actions reject false-success states', function (): void {
    $admin = adminUser();
    $verified = User::factory()->create();
    $unconfigured = User::factory()->unverified()->create();
    Notification::fake();

    $verificationActivities = Activity::query()->where('description', 'Verification email resent.')->count();
    $resetActivities = Activity::query()->where('description', 'Two-factor authentication reset.')->count();

    $this->actingAs($admin)
        ->post(route('admin.users.resendVerification', $verified))
        ->assertSessionHasErrors('email');
    $this->actingAs($admin)
        ->post(route('admin.users.resetTwoFactor', $unconfigured))
        ->assertSessionHasErrors('two_factor');

    Notification::assertNotSentTo($verified, VerifyEmail::class);
    expect(Activity::query()->where('description', 'Verification email resent.')->count())->toBe($verificationActivities)
        ->and(Activity::query()->where('description', 'Two-factor authentication reset.')->count())->toBe($resetActivities);
});

test('users without admin access cannot invoke admin user actions', function (): void {
    $user = User::factory()->create();
    $target = User::factory()->unverified()->create();

    $this->actingAs($user)->post(route('admin.users.resendVerification', $target))->assertForbidden();
    $this->actingAs($user)->post(route('admin.users.resetTwoFactor', $target))->assertForbidden();
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

    $this->actingAs($admin)
        ->get(route('admin.settings.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Admin/Settings/Index')
            ->has('setting'));

    $this->actingAs($admin)->patch(route('admin.settings.update'), [
        'company_name' => 'Southeast Code LLC',
        'notification_email' => 'ops@example.com',
        'default_task_due_days' => 5,
    ])->assertRedirect();

    expect(BusinessSetting::current()->company_name)->toBe('Southeast Code LLC')
        ->and(BusinessSetting::current()->default_task_due_days)->toBe(5);

    $activity = Activity::query()->where('description', 'Business settings updated.')->latest('id')->sole();

    expect($activity->properties->get('changed_fields'))->toContain('notification_email')
        ->and($activity->properties->toArray())->not->toHaveKey('notification_email_value');
});

test('audit filters cover notes and CRM activities without exposing sensitive values', function (): void {
    $admin = adminUser();
    $lead = Lead::factory()->create();

    $this->actingAs($admin);

    $note = $lead->notes()->create([
        'user_id' => $admin->id,
        'body' => 'Private contact details that must not be copied into audit history.',
    ]);
    $crmActivity = $lead->activities()->create([
        'user_id' => $admin->id,
        'type' => 'note_added',
        'description' => 'Sensitive internal activity description.',
        'properties' => ['private_token' => 'do-not-store'],
    ]);
    activity()
        ->useLog('crm')
        ->performedOn($note)
        ->event('note_reviewed')
        ->log('audit-filter-marker');

    $noteAudit = Activity::query()
        ->where('subject_type', Note::class)
        ->where('subject_id', $note->getKey())
        ->latest('id')
        ->firstOrFail();
    $crmActivityAudit = Activity::query()
        ->where('subject_type', CrmActivity::class)
        ->where('subject_id', $crmActivity->getKey())
        ->latest('id')
        ->firstOrFail();

    expect($noteAudit->attribute_changes?->toArray()['attributes'] ?? [])
        ->not->toHaveKey('body');
    expect($crmActivityAudit->attribute_changes?->toArray()['attributes'] ?? [])
        ->not->toHaveKey('description');
    expect($crmActivityAudit->attribute_changes?->toArray()['attributes'] ?? [])
        ->not->toHaveKey('properties');

    $auditDate = now()->toDateString();

    $this->get(route('admin.audit.index', [
        'search' => 'audit-filter-marker',
        'subject' => [Note::class],
        'from' => $auditDate,
        'to' => $auditDate,
    ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('filters.search', 'audit-filter-marker')
            ->where('filters.subject', [Note::class])
            ->where('filters.from', $auditDate)
            ->where('filters.to', $auditDate)
            ->where('activities.data.0.description', 'audit-filter-marker'));
});

test('audit filters expose constrained catalogs and support grouped array selections', function (): void {
    $admin = adminUser();
    $secondUser = User::factory()->create(['name' => $admin->name]);
    $lead = Lead::factory()->create();
    $contact = Contact::factory()->create();

    activity()->causedBy($admin)->performedOn($lead)->event('created')->log('lead-created');
    activity()->causedBy($secondUser)->performedOn($contact)->event('updated')->log('contact-updated');
    activity()->causedBy(null)->event('system-check')->log('system-check');
    activity()->causedBy(null)->log('system-no-subject');

    $response = $this->actingAs($admin)->get(route('admin.audit.index', [
        'event' => ['created', 'updated', '__unspecified__'],
        'causer' => [$admin->getKey(), '__system__'],
        'subject' => [Lead::class, '__no_subject__'],
    ]));

    $response->assertOk()->assertInertia(fn (Assert $page): Assert => $page
        ->where('filters.event', ['created', 'updated', '__unspecified__'])
        ->where('filters.causer', [(string) $admin->getKey(), '__system__'])
        ->where('filters.subject', [Lead::class, '__no_subject__'])
        ->where('eventOptions.0', ['label' => 'Unspecified', 'value' => '__unspecified__'])
        ->where('causerOptions.0', ['label' => 'System', 'value' => '__system__'])
        ->where('causerOptions.1.label', "{$admin->name} (#{$admin->getKey()})")
        ->where('subjectOptions.0', ['label' => 'No subject', 'value' => '__no_subject__']));
});

test('unknown audit filter selections cannot broaden results and scalar events remain supported', function (): void {
    $admin = adminUser();
    activity()->causedBy($admin)->event('created')->log('known-event');

    $this->actingAs($admin)->get(route('admin.audit.index', ['event' => 'created']))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('filters.event', ['created'])
            ->where('activities.data', fn (mixed $data): bool => collect($data)->pluck('description')->contains('known-event')));

    $this->actingAs($admin)->get(route('admin.audit.index', ['event' => ['not-a-real-event']]))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('filters.event', ['not-a-real-event'])
            ->where('activities.data', []));
});

test('audit filters reject nested tokens and invalid date ranges', function (): void {
    $admin = adminUser();

    $this->actingAs($admin)->get(route('admin.audit.index', ['event' => [['created']]]))
        ->assertSessionHasErrors('event.0');
    $this->actingAs($admin)->get(route('admin.audit.index', ['causer' => [['123']]]))
        ->assertSessionHasErrors('causer.0');
    $this->actingAs($admin)->get(route('admin.audit.index', ['subject' => ['']]))
        ->assertSessionHasErrors('subject.0');
    $this->actingAs($admin)->get(route('admin.audit.index', ['from' => '2026-02-30']))
        ->assertSessionHasErrors('from');
    $this->actingAs($admin)->get(route('admin.audit.index', ['from' => '2026-07-24', 'to' => '2026-07-23']))
        ->assertSessionHasErrors(['from', 'to']);
});

test('audit filter options are cached for five minutes before being refreshed', function (): void {
    $admin = adminUser();
    Cache::forget('activity-log.filter-options.v1');

    activity()->causedBy($admin)->event('initial-event')->log('initial-event');

    $this->actingAs($admin)->get(route('admin.audit.index'));

    activity()->causedBy($admin)->event('later-event')->log('later-event');

    $this->actingAs($admin)->get(route('admin.audit.index'))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('eventOptions', fn (mixed $options): bool => ! collect($options)->contains(['label' => 'Later Event', 'value' => 'later-event'])));

    $this->travel(5)->minutes();

    $this->actingAs($admin)->get(route('admin.audit.index'))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('eventOptions', fn (mixed $options): bool => collect($options)->contains(['label' => 'Later Event', 'value' => 'later-event'])));
});

test('activity log created at index is present with its stable name', function (): void {
    $index = collect(Schema::getIndexes('activity_log'))->firstWhere('name', 'activity_log_created_at_index');

    expect($index)->not->toBeNull()
        ->and($index['columns'])->toBe(['created_at'])
        ->and($index['unique'])->toBeFalse();
});

test('super admins bypass permission checks while protected role permissions remain immutable', function (): void {
    $superAdmin = adminUser(RoleName::SuperAdmin->value);
    $role = Role::findByName(RoleName::Admin->value);
    $protectedRole = Role::findByName(RoleName::SuperAdmin->value);
    $protectedPermissionIds = $protectedRole->permissions->pluck('id')->sort()->values()->all();

    $this->actingAs($superAdmin)->get(route('admin.audit.index'))->assertOk();
    $this->actingAs($superAdmin)->patch(route('admin.roles.update', $role), [
        'permission_ids' => Permission::query()->pluck('id')->all(),
    ])->assertRedirect();

    $this->actingAs($superAdmin)->patch(route('admin.roles.update', Role::findByName(RoleName::SuperAdmin->value)), [
        'permission_ids' => [],
    ])->assertSessionHasErrors('permission_ids');

    expect($protectedRole->refresh()->permissions->pluck('id')->sort()->values()->all())->toBe($protectedPermissionIds);

    $protectedRole->syncPermissions([]);
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    expect($superAdmin->can('jobs.manage'))->toBeTrue();
});

test('the role permission action protects Super Admin outside the controller', function (): void {
    $protectedRole = Role::findByName(RoleName::SuperAdmin->value);
    $permissionIds = $protectedRole->permissions->pluck('id')->all();
    $activityCount = Activity::query()->where('description', 'Role permissions updated.')->count();

    expect(fn (): mixed => app(UpdateRolePermissions::class)->handle(
        $protectedRole,
        RolePermissionsData::fromValidated(['permission_ids' => []]),
    ))->toThrow(ValidationException::class);

    expect($protectedRole->refresh()->permissions->pluck('id')->all())->toBe($permissionIds)
        ->and(Activity::query()->where('description', 'Role permissions updated.')->count())->toBe($activityCount)
        ->and(RolePermissionsData::fromValidated([])->permissionIds)->toBe([])
        ->and(RolePermissionsData::fromValidated(['permission_ids' => null])->permissionIds)->toBe([]);
});
