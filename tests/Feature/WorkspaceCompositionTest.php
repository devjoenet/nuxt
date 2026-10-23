<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use SoutheastCode\Audit\Contracts\ActivityPresenterRegistry;
use SoutheastCode\Crm\Contacts\Models\Contact;
use SoutheastCode\Crm\Leads\Models\Lead;
use SoutheastCode\Modules\ModuleManifest;
use SoutheastCode\Modules\ModuleRegistry;
use SoutheastCode\Modules\WorkspaceComposition;
use Spatie\Activitylog\Models\Activity;

beforeEach(function (): void {
    app(RolePermissionSeeder::class)->run();
    $this->withoutVite();
});

dataset('composition modules', [
    'foundation' => [['foundation']],
    'audit' => [['foundation', 'audit']],
    'users' => [['foundation', 'audit', 'users']],
    'crm' => [['foundation', 'audit', 'crm']],
    'development' => [['foundation', 'audit', 'dev']],
    'full' => [['foundation', 'audit', 'users', 'crm', 'dev']],
]);

test('registered modules determine navigation dashboard summaries and user context', function (array $enabled): void {
    $admin = User::factory()->create()->assignRole('Admin');
    $registry = new ModuleRegistry;
    foreach (app(ModuleRegistry::class)->all() as $module) {
        if (in_array($module->id === 'crm-host' ? 'crm' : $module->id, $enabled, true)) {
            $registry->register($module);
        }
    }
    app()->instance(ModuleRegistry::class, $registry);
    $this->actingAs($admin)->get(route('dashboard'))->assertOk()->assertInertia(function (Assert $page) use ($enabled): void {
        $page->where('workspace.navigation', function ($navigation) use ($enabled): bool {
            $labels = collect($navigation)->pluck('label');

            return $labels->contains('CRM') === in_array('crm', $enabled, true)
                && $labels->contains('Development') === in_array('dev', $enabled, true);
        });
        if (in_array('crm', $enabled, true)) {
            $page->where('canViewCrm', true)->has('stats', 4)->where('dashboardWidgets', ['crm']);
        } else {
            $page->missing('recentLeads')->missing('upcomingTasks')->missing('canViewCrm');
        }
        if (in_array('dev', $enabled, true)) {
            $page->where('auth.user.has_pending_development_tests', false);
        } else {
            $page->missing('auth.user.has_pending_development_tests');
        }
    });
    expect(app(WorkspaceComposition::class)->adminStats($admin))->toHaveCount(in_array('crm', $enabled, true) ? 2 : 0);
})->with('composition modules');

test('unrelated pages never run CRM dashboard summary queries', function (): void {
    $admin = User::factory()->create()->assignRole('Admin');
    DB::enableQueryLog();
    $this->actingAs($admin)->get(route('profile.edit'))->assertOk();
    $queries = collect(DB::getQueryLog())->pluck('query')->implode(' ');
    DB::disableQueryLog();
    expect($queries)->not->toContain('"leads"')->not->toContain('"follow_up_tasks"');
});

test('lead badge display labels keep stored values and permission scoping', function (): void {
    $viewer = User::factory()->create();
    $viewer->givePermissionTo('crm.view');
    Lead::factory()->create(['owner_id' => $viewer->id, 'status' => 'qualified', 'created_at' => now()->subMinute()]);
    Lead::factory()->create(['owner_id' => $viewer->id, 'status' => 'new']);
    $this->actingAs($viewer)->get(route('dashboard'))->assertInertia(fn (Assert $page): Assert => $page
        ->where('recentLeads.0.status', 'new')->where('recentLeads.0.status_label', 'New')
        ->where('recentLeads.1.status', 'qualified')->where('recentLeads.1.status_label', 'Qualified'));
});

test('server navigation preserves section and detail breadcrumbs without private route imports', function (): void {
    $viewer = User::factory()->create()->assignRole('Admin');
    $lead = Lead::factory()->create();
    $this->actingAs($viewer)->get(route('crm.leads.show', $lead))->assertInertia(fn (Assert $page): Assert => $page
        ->where('workspace.breadcrumbs', [['label' => 'CRM', 'href' => '/crm/leads'], ['label' => 'Leads', 'href' => '/crm/leads']]));
    $task = App\Models\DevelopmentTask::factory()->create();
    $this->get(route('development.tasks.show', $task))->assertInertia(fn (Assert $page): Assert => $page
        ->where('workspace.breadcrumbs', [['label' => 'Development', 'href' => '/development']]));
    $this->get(route('admin.users.index'))->assertInertia(fn (Assert $page): Assert => $page
        ->where('workspace.breadcrumbs.0.label', 'Admin'));
});

test('recent changes retain a global limit and deterministic order across domain presenters', function (): void {
    $admin = User::factory()->create()->assignRole('Admin');
    $contact = Contact::factory()->create();
    Activity::query()->delete();
    foreach (range(1, 12) as $number) {
        activity()->causedBy($admin)->performedOn($number % 2 === 0 ? $contact : $admin)->event('updated')->log('Updated');
    }
    $expected = Activity::query()->orderByDesc('created_at')->orderByDesc('id')->limit(8)->pluck('id')->all();
    expect(app(SoutheastCode\Audit\Queries\RecentAdminChangesQuery::class)->forUser($admin)->pluck('id')->all())->toBe($expected);
});

test('disabled activity providers hide domain changes even when the user retains grants', function (): void {
    $admin = User::factory()->create()->assignRole('Admin');
    $contact = Contact::factory()->create(['name' => 'Absent module contact']);
    activity()->performedOn($contact)->event('updated')->log('updated');
    $registry = new ModuleRegistry;
    $registry->register(app(ModuleRegistry::class)->all()['foundation']);
    app()->instance(ModuleRegistry::class, $registry);
    expect(app(SoutheastCode\Audit\Queries\RecentAdminChangesQuery::class)->forUser($admin))->toBeEmpty();
    $entry = Activity::query()->where('subject_type', $contact->getMorphClass())->latest('id')->firstOrFail();
    $entry->setRelation('subject', null);
    $presentation = app(ActivityPresenterRegistry::class)->present($entry, $admin);
    expect($presentation->label)->toContain('Unavailable record')->and($presentation->url)->toBeNull();
});

test('invalid contribution contracts fail during registration', function (): void {
    expect(fn () => (new ModuleRegistry)->register(new ModuleManifest('broken', workspaceDashboard: stdClass::class)))
        ->toThrow(LogicException::class, 'Invalid workspaceDashboard');
});

test('composition rejects overlapping response navigation and activity ownership', function (): void {
    $admin = User::factory()->create()->assignRole('Admin');
    $registry = new ModuleRegistry;
    $registry->register(new ModuleManifest('one', navigation: [App\Modules\Contributions\FoundationNavigation::class], workspaceDashboard: SoutheastCode\Crm\Shared\Queries\CrmWorkspaceDashboardQuery::class, extensions: ['audit.presenters' => [App\Audit\Presenters\FoundationActivityPresenter::class]]));
    $registry->register(new ModuleManifest('two', navigation: [App\Modules\Contributions\FoundationNavigation::class], workspaceDashboard: SoutheastCode\Crm\Shared\Queries\CrmWorkspaceDashboardQuery::class, extensions: ['audit.presenters' => [App\Audit\Presenters\FoundationActivityPresenter::class]]));
    $composition = new WorkspaceComposition($registry);
    expect(fn () => $composition->dashboard($admin))->toThrow(LogicException::class, 'Conflicting module response property');
    expect(fn () => $composition->navigation($admin, request()))->toThrow(LogicException::class, 'Conflicting navigation key');
    expect(fn () => (new ActivityPresenterRegistry($registry))->presenters())->toThrow(LogicException::class, 'Conflicting activity presenter');
});

test('Development activity visibility includes participants and reviewers without widening the Admin feed', function (): void {
    $participant = User::factory()->create();
    $other = User::factory()->create();
    $owned = App\Models\DevelopmentTask::factory()->create(['submitter_id' => $participant->id]);
    $assigned = App\Models\DevelopmentTask::factory()->create(['submitter_id' => $other->id, 'assignee_id' => $participant->id]);
    $tested = App\Models\DevelopmentTask::factory()->create(['submitter_id' => $other->id]);
    App\Models\DevelopmentTestRun::factory()->create(['development_task_id' => $tested->id, 'tester_id' => $participant->id]);
    $hidden = App\Models\DevelopmentTask::factory()->create(['submitter_id' => $other->id]);
    Activity::query()->delete();
    foreach ([$owned, $assigned, $tested, $hidden] as $task) {
        activity()->performedOn($task)->event('updated')->log('updated');
    }
    $presenter = app(App\Audit\Presenters\DevelopmentActivityPresenter::class);
    $query = Activity::query();
    $presenter->constrain($query, $participant);
    expect($query->pluck('subject_id')->all())->toEqualCanonicalizing([$owned->id, $assigned->id, $tested->id]);
    $participant->givePermissionTo('development.requests.review');
    $query = Activity::query();
    $presenter->constrain($query, $participant);
    expect($query->count())->toBe(4);
    $entry = Activity::query()->where('subject_id', $owned->id)->firstOrFail();
    expect($presenter->present($entry, $participant)->url)->toBe(route('development.tasks.history', $owned));
    $owned->forceDelete();
    $entry->unsetRelation('subject');
    expect($presenter->present($entry, $participant)->url)->toBeNull();
    expect(app(SoutheastCode\Audit\Queries\RecentAdminChangesQuery::class)->forUser($participant))->toBeEmpty();
});
