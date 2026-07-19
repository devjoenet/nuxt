<?php

declare(strict_types=1);

use App\Enums\RoleName;
use App\Models\BusinessSetting;
use App\Models\Contact;
use App\Models\CrmActivity;
use App\Models\Lead;
use App\Models\Note;
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
