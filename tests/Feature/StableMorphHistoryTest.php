<?php

declare(strict_types=1);

use App\Actions\CreateLeadNote;
use App\Actions\CreateRole;
use App\Data\CreateRoleData;
use App\Data\LeadNoteData;
use App\Models\BusinessSetting;
use App\Models\Contact;
use App\Models\CrmActivity;
use App\Models\DevelopmentTask;
use App\Models\DevelopmentTaskLink;
use App\Models\DevelopmentTaskNote;
use App\Models\DevelopmentTestRun;
use App\Models\FollowUpTask;
use App\Models\Lead;
use App\Models\Note;
use App\Models\Organization;
use App\Models\User;
use App\Models\UserTestingPage;
use App\Queries\LeadHistoryQuery;
use Database\Seeders\CrmDemoSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\ClassMorphViolationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Inertia\Testing\AssertableInertia as Assert;
use SoutheastCode\Audit\Data\ActivityLogFiltersData;
use SoutheastCode\Audit\Queries\ActivityLogQuery;
use SoutheastCode\Audit\Queries\RecordHistoryQuery;
use SoutheastCode\Audit\Support\AuditMorphs;
use SoutheastCode\Modules\ModuleManifest;
use SoutheastCode\Modules\ModuleRegistry;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    $this->withoutVite();
    if (config('modules.stable_morphs')) {
        expect(Relation::requiresMorphMap())->toBeTrue();
    }
    app(ModuleRegistry::class)->enforceMorphMap();
});

afterEach(function (): void {
    Relation::morphMap([], false);
    Relation::requireMorphMap(false);
});

test('strict aliases cover every active application and third party model', function (string $class, string $alias): void {
    $model = new $class;

    expect($model->getMorphClass())->toBe($alias);
    expect(Relation::getMorphedModel($alias))->toBe($class);
})->with([
    [User::class, 'user'], [BusinessSetting::class, 'business-setting'],
    [Role::class, 'role'], [Permission::class, 'permission'], [Activity::class, 'audit-activity'],
    [Organization::class, 'crm-organization'], [Contact::class, 'crm-contact'],
    [Lead::class, 'crm-lead'], [FollowUpTask::class, 'crm-follow-up-task'],
    [Note::class, 'crm-note'], [CrmActivity::class, 'crm-activity'],
    [DevelopmentTask::class, 'development-task'], [DevelopmentTaskLink::class, 'development-task-link'],
    [DevelopmentTaskNote::class, 'development-task-note'], [DevelopmentTestRun::class, 'development-test-run'],
    [UserTestingPage::class, 'user-testing-page'],
]);

test('strict writes reject an unregistered model', function (): void {
    $unknown = new class extends Model {};

    expect(fn () => activity()->performedOn($unknown)->log('Unsupported'))
        ->toThrow(ClassMorphViolationException::class);
});

test('fresh role seeding and direct grants use the same user alias', function (): void {
    app(RolePermissionSeeder::class)->run();
    app(RolePermissionSeeder::class)->run();
    $actor = User::factory()->create()->assignRole('Admin');
    $permission = Permission::findOrCreate('synthetic.export', 'web');
    $actor->givePermissionTo($permission);
    $role = app(CreateRole::class)->handle(new CreateRoleData('Synthetic role', [$permission->id]), $actor);

    $this->assertDatabaseHas('model_has_roles', ['model_id' => $actor->id, 'model_type' => 'user']);
    $this->assertDatabaseHas('model_has_permissions', ['model_id' => $actor->id, 'model_type' => 'user']);
    expect($actor->refresh()->can('admin.roles.manage'))->toBeTrue()
        ->and($actor->can('synthetic.export'))->toBeTrue();
    $audit = app(RecordHistoryQuery::class)->build($role)->sole();
    expect($audit->subject_type)->toBe('role')
        ->and($audit->causer_type)->toBe('user')
        ->and($audit->causer->is($actor))->toBeTrue();
    expect(app(ActivityLogQuery::class)->filterOptions()['causers'])
        ->toContain(['label' => $actor->name, 'value' => (string) $actor->id]);
});

test('new note history keeps aliased JSON association after permanent deletion', function (): void {
    $actor = User::factory()->create();
    $lead = Lead::factory()->create(['owner_id' => $actor->id]);
    $other = Lead::factory()->create();
    $note = app(CreateLeadNote::class)->handle($lead, new LeadNoteData('Synthetic note'), $actor);
    $otherNote = app(CreateLeadNote::class)->handle($other, new LeadNoteData('Other note'), $actor);
    $created = Activity::query()->forSubject($note)->where('event', 'created')->sole();
    expect($note->subject_type)->toBe('crm-lead');
    expect($created->attribute_changes->get('attributes')['subject_type'])->toBe('crm-lead');

    $note->delete();
    $note->forceDelete();
    $history = app(LeadHistoryQuery::class)->build($lead)->get();

    expect($history->pluck('id')->all())->toContain($created->id);
    expect(app(LeadHistoryQuery::class)->build($lead)->pluck('id')->all())->toContain($created->id);
    expect($history->where('subject_type', 'crm-note')->pluck('subject_id')->unique()->all())->toBe([$note->id]);
    expect($history->firstWhere('id', $created->id)->causer->is($actor))->toBeTrue();
    expect(Activity::query()->forSubject($otherNote)->exists())->toBeTrue();
});

test('old JSON association recovers a note moved before permanent deletion', function (): void {
    $lead = Lead::factory()->create();
    $other = Lead::factory()->create();
    $note = Note::factory()->create(['subject_id' => $lead->id]);
    $note->subject()->associate($other);
    $note->save();
    $moved = Activity::query()->forSubject($note)->where('event', 'updated')->sole();
    expect($moved->attribute_changes->get('old')['subject_id'])->toBe($lead->id);
    $note->forceDelete();

    expect(app(LeadHistoryQuery::class)->build($lead)->get()->pluck('id')->all())->toContain($moved->id);
});

test('soft deleted subjects retain history and actor labels', function (): void {
    $actor = User::factory()->create();
    $lead = Lead::factory()->create();
    $audit = activity()->performedOn($lead)->causedBy($actor)->log('Synthetic update');
    $lead->delete();

    $history = app(RecordHistoryQuery::class)->build($lead)->get();

    expect($history->pluck('id')->all())->toContain($audit->id);
    expect($history->firstWhere('id', $audit->id)->causer->name)->toBe($actor->name);
});

test('audit handles missing aliases and actors without resolving classes or bypassing authorization', function (): void {
    $viewer = User::factory()->create();
    $viewer->givePermissionTo(Permission::findOrCreate('audit.view', 'web'));
    $audit = Activity::query()->create([
        'description' => 'Removed module record', 'subject_type' => 'absent-record', 'subject_id' => 77,
        'causer_type' => 'absent-user', 'causer_id' => 88,
        'attribute_changes' => ['attributes' => ['status' => 'synthetic']],
    ]);

    $this->actingAs($viewer)->get(route('admin.audit.index'))->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('activities.data.0.id', $audit->id)
            ->where('activities.data.0.subject', 'Unavailable record')
            ->where('activities.data.0.causer', null));
    $this->actingAs(User::factory()->create())->get(route('admin.audit.index'))->assertForbidden();
});

test('an absent module contributes no aliases and its history remains safely unavailable', function (): void {
    $actor = User::factory()->create();
    $lead = Lead::factory()->create();
    $audit = activity()->performedOn($lead)->causedBy($actor)->log('Installed before removal');
    $registry = new ModuleRegistry;
    $registry->register(new ModuleManifest('foundation', morphMap: ['user' => User::class]));
    $this->app->instance(ModuleRegistry::class, $registry);
    Relation::morphMap([], false);
    $registry->enforceMorphMap();

    $loaded = app(ActivityLogQuery::class)->build(ActivityLogFiltersData::fromValidated([]))->get()->firstWhere('id', $audit->id);

    expect(Relation::getMorphedModel('crm-lead'))->toBeNull();
    expect($loaded->subject)->toBeNull()
        ->and($loaded->causer->is($actor))->toBeTrue();
    expect(AuditMorphs::label('crm-lead'))->toBe('Unavailable record');
});

test('registration rejects ambiguous model aliases non models and external collisions atomically', function (): void {
    $registry = new ModuleRegistry;
    $registry->register(new ModuleManifest('foundation', morphMap: ['person' => User::class]));
    expect(fn () => $registry->register(new ModuleManifest('duplicate', morphMap: ['other-person' => User::class])))
        ->toThrow(LogicException::class, 'Conflicting morph model');
    expect(fn () => $registry->register(new ModuleManifest('invalid', morphMap: ['invalid' => stdClass::class])))
        ->toThrow(LogicException::class, 'must extend');
    expect(array_keys($registry->all()))->toBe(['foundation']);
    expect(fn () => $registry->enforceMorphMap())->toThrow(LogicException::class, 'Conflicting registered morph model');
    expect((new User)->getMorphClass())->toBe('user');
});

test('demo seeding creates notes and history with aliases', function (): void {
    app(RolePermissionSeeder::class)->run();
    User::factory()->create();

    app(CrmDemoSeeder::class)->run();

    expect(Note::query()->exists())->toBeTrue();
    expect(Note::query()->where('subject_type', '!=', 'crm-lead')->exists())->toBeFalse();
    expect(Activity::query()->where('subject_type', 'like', '%\\\\%')->exists())->toBeFalse();
});

test('consumer combinations register only installed module aliases before bootstrap writes', function (array $installed): void {
    $full = app(ModuleRegistry::class);
    $registry = new ModuleRegistry;
    foreach ($installed as $id) {
        $registry->register($full->all()[$id]);
    }
    $this->app->instance(ModuleRegistry::class, $registry);
    Relation::morphMap([], false);
    $registry->enforceMorphMap();
    app(RolePermissionSeeder::class)->run();
    $actor = User::factory()->create()->assignRole('Admin');

    expect($actor->refresh()->hasRole('Admin'))->toBeTrue();
    $this->assertDatabaseHas('model_has_roles', ['model_id' => $actor->id, 'model_type' => 'user']);
    foreach ($full->all() as $id => $manifest) {
        foreach ($manifest->morphMap as $alias => $class) {
            if (in_array($id, $installed, true)) {
                expect((new $class)->getMorphClass())->toBe($alias);
            } else {
                expect(Relation::getMorphedModel($alias))->toBeNull();
                expect(fn () => (new $class)->getMorphClass())->toThrow(ClassMorphViolationException::class);
            }
        }
    }
})->with([
    'foundation' => [['foundation']],
    'audit only' => [['foundation', 'audit']],
    'user management' => [['foundation', 'audit', 'users']],
    'CRM' => [['foundation', 'audit', 'crm']],
    'Development' => [['foundation', 'audit', 'dev']],
    'full' => [['foundation', 'audit', 'users', 'crm', 'dev']],
]);

class StableMorphConfiguredUser extends User
{
    protected $table = 'users';
}

test('configured user classes share one stable actor and permission identity', function (): void {
    config(['auth.providers.users.model' => StableMorphConfiguredUser::class]);
    $registry = new ModuleRegistry;
    foreach (app(ModuleRegistry::class)->all() as $manifest) {
        $registry->register($manifest->id === 'foundation'
            ? new ModuleManifest('foundation', $manifest->permissions, morphMap: array_replace($manifest->morphMap, ['user' => StableMorphConfiguredUser::class]))
            : $manifest);
    }
    $this->app->instance(ModuleRegistry::class, $registry);
    Relation::morphMap([], false);
    $registry->enforceMorphMap();
    app(RolePermissionSeeder::class)->run();
    $actor = User::factory()->create()->assignRole('Admin');
    $lead = Lead::factory()->create(['owner_id' => $actor->id]);
    $note = app(CreateLeadNote::class)->handle($lead, new LeadNoteData('Configured actor'), $actor);

    $audit = app(RecordHistoryQuery::class)->build($note)->sole();

    expect($audit->causer_type)->toBe('user')
        ->and($audit->causer)->toBeInstanceOf(StableMorphConfiguredUser::class)
        ->and($audit->causer->is($actor))->toBeTrue();
    expect($actor->refresh()->can('crm.manage'))->toBeTrue();
    $this->assertDatabaseHas('model_has_roles', ['model_id' => $actor->id, 'model_type' => 'user']);
});
