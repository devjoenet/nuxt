<?php

declare(strict_types=1);

use App\Enums\RoleName;
use App\Models\User;
use App\Support\WorkspaceUsers;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Notification;
use SoutheastCode\Crm\Leads\Actions\CreateLeadNote;
use SoutheastCode\Crm\Leads\Data\LeadNoteData;
use SoutheastCode\Crm\Leads\Models\Lead;
use SoutheastCode\Modules\ModuleManifest;
use SoutheastCode\Modules\ModuleRegistry;
use SoutheastCode\Modules\PermissionDefinition;
use SoutheastCode\UserManagement\Actions\AcceptUserInvitation;
use SoutheastCode\UserManagement\Actions\InviteUser;
use SoutheastCode\UserManagement\Data\InviteUserData;
use SoutheastCode\UserManagement\Notifications\UserInvitationNotification;
use SoutheastCode\UserManagement\Queries\RolePermissionCatalogQuery;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ConfiguredWorkspaceUser extends User
{
    protected $table = 'users';
}

test('an absent module preserves persisted grants and remains editable after repeated seeding', function (): void {
    $permission = Permission::findOrCreate('absent.export', 'web');
    $custom = Role::findOrCreate('Custom operator', 'web')->givePermissionTo($permission);
    $admin = Role::findOrCreate(RoleName::Admin->value, 'web')->givePermissionTo($permission);
    $registry = new ModuleRegistry;
    $registry->register(new ModuleManifest('foundation', [
        new PermissionDefinition('jobs.view', 'View', 'jobs', 'Jobs & queues', [RoleName::Admin->value]),
    ]));
    $this->app->instance(ModuleRegistry::class, $registry);

    app(RolePermissionSeeder::class)->run();
    app(RolePermissionSeeder::class)->run();

    expect($custom->refresh()->permissions->pluck('name')->all())->toBe(['absent.export']);
    expect($admin->refresh()->hasPermissionTo('absent.export'))->toBeTrue()
        ->and($admin->hasPermissionTo('jobs.view'))->toBeTrue();
    expect(collect(app(RolePermissionCatalogQuery::class)->grouped())->firstWhere('key', 'other')['permissions'])->toContain(
        ['id' => $permission->id, 'name' => 'absent.export', 'label' => 'Absent Export'],
    );
});

test('registered extensions reach the role catalog and capability payload', function (): void {
    $registry = new ModuleRegistry;
    $registry->register(new ModuleManifest('reports', [
        new PermissionDefinition('reports.export', 'Export reports', 'reports', 'Reports', ['Reporter']),
    ]));
    $this->app->instance(ModuleRegistry::class, $registry);
    app(RolePermissionSeeder::class)->run();
    $user = User::factory()->create()->assignRole('Reporter');

    $payload = (new App\Http\Resources\UserResource($user))->toArray(request());

    expect($payload['can'])->toBe(['reports.export' => true]);
    expect(collect(app(RolePermissionCatalogQuery::class)->grouped())->firstWhere('key', 'reports')['label'])->toBe('Reports');
    expect($registry->has('crm'))->toBeFalse();
});

test('module registration rejects collisions atomically', function (string $kind): void {
    $registry = new ModuleRegistry;
    $permission = new PermissionDefinition('reports.export', 'Export', 'reports', 'Reports');
    $first = new ModuleManifest('first', [$permission], 'reports', ['reports.index'], ['reports'], ['person' => User::class], ['reports/public']);
    $registry->register($first);
    $second = match ($kind) {
        'module' => new ModuleManifest('first'),
        'permission' => new ModuleManifest('second', [$permission]),
        'page' => new ModuleManifest('second', pageNamespace: 'reports'),
        'route' => new ModuleManifest('second', routeNames: ['reports.index']),
        'navigation' => new ModuleManifest('second', navigationKeys: ['reports']),
        'morph' => new ModuleManifest('second', morphMap: ['person' => User::class]),
        'export' => new ModuleManifest('second', frontendExports: ['reports/public']),
    };

    expect(fn () => $registry->register($second))->toThrow(LogicException::class);
    expect(array_keys($registry->all()))->toBe(['first']);
})->with(['module', 'permission', 'page', 'route', 'navigation', 'morph', 'export']);

test('module registration closes after providers boot', function (): void {
    expect(fn () => app(ModuleRegistry::class)->register(new ModuleManifest('late')))
        ->toThrow(LogicException::class, 'closed');
});

test('module registration rejects incompatible versions and missing morph models', function (): void {
    $registry = new ModuleRegistry;

    expect(fn () => $registry->register(new ModuleManifest('future', manifestVersion: 2)))
        ->toThrow(LogicException::class, 'Unsupported');
    expect(fn () => $registry->register(new ModuleManifest('future', hostApiVersion: 2)))
        ->toThrow(LogicException::class, 'Unsupported');
    expect(fn () => $registry->register(new ModuleManifest('missing', morphMap: ['missing' => 'MissingModel'])))
        ->toThrow(LogicException::class, 'Missing morph model');
    expect($registry->all())->toBeEmpty();
});

test('configured workspace users retain identity relationships invitations and explicit actors', function (): void {
    config(['auth.providers.users.model' => ConfiguredWorkspaceUser::class]);
    app(RolePermissionSeeder::class)->run();
    Notification::fake();
    $actor = WorkspaceUsers::model()::factory()->create()->assignRole(RoleName::Admin->value);
    $lead = Lead::factory()->create(['owner_id' => $actor->workspaceId()]);

    $user = app(InviteUser::class)->handle(new InviteUserData('Invited', 'invited@example.com', RoleName::SalesOps->value), $actor);
    app(AcceptUserInvitation::class)->handle($user);
    app(AcceptUserInvitation::class)->handle($user);
    $note = app(CreateLeadNote::class)->handle($lead, new LeadNoteData('Configured actor'), $actor);

    expect($actor)->toBeInstanceOf(ConfiguredWorkspaceUser::class)
        ->and($actor->workspaceUuid())->toBe($actor->uuid)
        ->and($actor->workspaceName())->toBe($actor->name)
        ->and($actor->workspaceEmail())->toBe($actor->email)
        ->and($actor->workspaceActive())->toBeTrue()
        ->and($actor->workspaceCan('crm.manage'))->toBeTrue();
    expect($lead->owner)->toBeInstanceOf(ConfiguredWorkspaceUser::class);
    expect($note->user)->toBeInstanceOf(ConfiguredWorkspaceUser::class);
    expect($user)->toBeInstanceOf(ConfiguredWorkspaceUser::class)
        ->and($user->refresh()->invitation_accepted_at)->not->toBeNull();
    expect(Activity::query()->where('description', 'User invitation sent.')->sole()->causer_id)->toBe($actor->id);
    Notification::assertSentTo($user, UserInvitationNotification::class);
});

test('invalid workspace model configuration fails clearly', function (): void {
    config(['auth.providers.users.model' => stdClass::class]);

    expect(fn () => WorkspaceUsers::model())->toThrow(LogicException::class, 'configured workspace user');
});
