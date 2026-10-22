<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Inertia\Testing\AssertableInertia as Assert;
use SoutheastCode\Crm\Contacts\Models\Contact;
use SoutheastCode\Crm\FollowUps\Models\FollowUpTask;
use SoutheastCode\Crm\Leads\Models\Lead;
use SoutheastCode\UserManagement\Actions\InviteUser;
use SoutheastCode\UserManagement\Data\InviteUserData;

beforeEach(function (): void {
    app(RolePermissionSeeder::class)->run();
    $this->withoutVite();
});

test('overview counts match actionable filtered destinations', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $unassigned = Lead::factory()->create(['owner_id' => null]);
    $assigned = Lead::factory()->create(['owner_id' => $admin->id]);
    $archived = Lead::factory()->create(['owner_id' => null, 'archived_at' => now()]);
    FollowUpTask::factory()->create(['lead_id' => $assigned->id, 'due_at' => now()->subDay()]);
    FollowUpTask::factory()->create(['lead_id' => $assigned->id, 'due_at' => now()->addDay()]);
    FollowUpTask::factory()->create(['lead_id' => $archived->id, 'due_at' => now()->subDay()]);
    User::factory()->unverified()->create();
    User::factory()->create(['invited_at' => now()]);
    User::factory()->create(['invited_at' => now(), 'invitation_accepted_at' => now()]);
    User::factory()->create(['invited_at' => now(), 'is_active' => false]);

    $this->actingAs($admin)->get(route('admin.dashboard'))->assertInertia(fn (Assert $page): Assert => $page
        ->has('stats', 5)
        ->where('stats.1.value', 1)
        ->where('stats.2.value', 1)
        ->where('stats.3.value', 1));
    $this->get(route('crm.leads.index', ['unassigned' => 1]))->assertInertia(fn (Assert $page): Assert => $page
        ->has('leads.data', 1)->where('leads.data.0.id', $unassigned->uuid)->where('filters.unassigned', '1'));
    $this->get(route('crm.leads.index', ['overdue' => 1]))->assertInertia(fn (Assert $page): Assert => $page
        ->has('leads.data', 1)->where('leads.data.0.id', $assigned->uuid));
    $this->get(route('admin.users.index', ['status' => 'pending']))->assertInertia(fn (Assert $page): Assert => $page->has('users.data', 1)->where('filters.status', 'pending'));
    $this->get(route('admin.users.index', ['status' => 'invalid']))->assertSessionHasErrors('status');
});

test('overview hides unauthorized data and limits CRM changes to visible records', function (): void {
    $viewer = User::factory()->create();
    $viewer->givePermissionTo(['admin.dashboard.view']);
    $this->actingAs($viewer)->get(route('admin.dashboard'))->assertInertia(fn (Assert $page): Assert => $page
        ->where('stats', [])->where('recentChanges', [])->where('canViewChanges', false));
    $viewer->givePermissionTo(['crm.view', 'audit.view']);
    $visible = Contact::factory()->create(['owner_id' => $viewer->id, 'name' => 'Visible contact']);
    $hidden = Contact::factory()->create(['owner_id' => User::factory()->create()->id, 'name' => 'Hidden contact']);
    activity()->causedBy($viewer)->performedOn($visible)->event('updated')->log('updated');
    activity()->causedBy($viewer)->performedOn($hidden)->event('updated')->log('updated');

    $this->get(route('admin.dashboard'))->assertInertia(fn (Assert $page): Assert => $page
        ->has('stats', 2)
        ->where('recentChanges', fn ($changes): bool => collect($changes)->contains(fn ($change): bool => $change['record'] === 'Contact · Visible contact')
            && ! collect($changes)->contains(fn ($change): bool => str_contains($change['record'], 'Hidden contact'))));
});

test('failed job storage outages remain unavailable instead of zero', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    config(['queue.failed.table' => 'missing_failed_jobs']);
    $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->assertInertia(fn (Assert $page): Assert => $page
        ->where('stats.0.value', null)->where('stats.0.attention', true));
});

test('invitations become accepted only after a successful password setup', function (): void {
    Notification::fake();
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $invited = app(InviteUser::class)->handle(InviteUserData::fromValidated([
        'name' => 'Invited staff', 'email' => 'invited@example.com', 'role' => App\Enums\RoleName::SalesOps->value,
    ]), $admin);
    expect(User::query()->pendingInvitation()->whereKey($invited)->exists())->toBeTrue();
    $payload = ['email' => $invited->email, 'password' => 'password', 'password_confirmation' => 'password'];
    $this->post(route('password.store'), [...$payload, 'token' => 'invalid'])->assertSessionHasErrors('email');
    expect($invited->refresh()->invitation_accepted_at)->toBeNull();
    $token = Password::createToken($invited);
    $this->post(route('password.store'), [...$payload, 'token' => $token])->assertSessionHasNoErrors();
    expect($invited->refresh()->invitation_accepted_at)->not->toBeNull()
        ->and(User::query()->pendingInvitation()->whereKey($invited)->exists())->toBeFalse()
        ->and($invited->email_verified_at)->toBeNull();
});

test('legacy invitation uncertainty is disclosed rather than reported as zero', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $legacy = User::factory()->unverified()->create();
    activity()->causedBy($admin)->performedOn($legacy)->log('User invitation sent.');
    $this->actingAs($admin)->get(route('admin.dashboard'))->assertInertia(fn (Assert $page): Assert => $page->where('stats.3.value', null));
    $this->get(route('admin.users.index', ['status' => 'pending']))->assertInertia(fn (Assert $page): Assert => $page->where('hasUntrackedInvitations', true));
});

test('recent access settings and CRM changes are readable and lead activity links resolve into History', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $lead = Lead::factory()->create();
    $crmActivity = SoutheastCode\Crm\Shared\Models\CrmActivity::factory()->create(['lead_id' => $lead->id]);
    $setting = App\Models\BusinessSetting::current();
    $role = Spatie\Permission\Models\Role::findByName('Admin');
    activity()->causedBy($admin)->performedOn($crmActivity)->event('updated')->log('updated');
    activity()->causedBy($admin)->performedOn($setting)->log('Business settings updated.');
    activity()->causedBy($admin)->performedOn($role)->log('Role permissions updated.');
    $this->actingAs($admin)->get(route('admin.dashboard'))->assertInertia(fn (Assert $page): Assert => $page
        ->where('recentChanges.0.actor', $admin->name)
        ->where('recentChanges.0.action', 'Role Permissions Updated.')
        ->where('recentChanges.0.record', 'Role · Admin')
        ->where('recentChanges.1.record', 'Business settings')
        ->where('recentChanges.2.url', route('crm.leads.history', $lead)))
        ->assertDontSee('private_token');
    $this->get(route('crm.leads.history', $lead))->assertInertia(fn (Assert $page): Assert => $page
        ->where('activities.data', fn ($entries): bool => collect($entries)->contains(fn ($entry): bool => $entry['subject'] === 'Crm Activity')));
});
