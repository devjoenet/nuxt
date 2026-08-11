<?php

declare(strict_types=1);

use App\Enums\DevelopmentTaskLinkType;
use App\Enums\DevelopmentTaskStatus;
use App\Enums\Permission as DevelopmentPermission;
use App\Enums\TaskPriority;
use App\Models\DevelopmentTask;
use App\Models\DevelopmentTaskLink;
use App\Models\User;
use Inertia\Testing\AssertableInertia;
use Spatie\Activitylog\Enums\ActivityEvent;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function developmentRoadmapUser(
    DevelopmentPermission $permission,
    string $roleName,
): User {
    $role = Role::findOrCreate($roleName, 'web');
    $role->givePermissionTo(Permission::findOrCreate($permission->value, 'web'));
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

beforeEach(function (): void {
    $this->withoutVite();
});

test('roadmap managers see database-backed tasks grouped by active roadmap status', function (): void {
    $manager = developmentRoadmapUser(
        DevelopmentPermission::DevelopmentRoadmapManage,
        'Roadmap Board Manager',
    );
    $planned = DevelopmentTask::factory()->planned()->create(['roadmap_position' => 2]);
    $inProgress = DevelopmentTask::factory()->inProgress()->create(['roadmap_position' => 1]);
    $blocked = DevelopmentTask::factory()->blocked()->create(['roadmap_position' => 3]);
    DevelopmentTask::factory()->create();

    $this->actingAs($manager)
        ->get(route('development.roadmap.index'))
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Development/Roadmap/Index/Page')
            ->has('tasks.planned', 1)
            ->where('tasks.planned.0.id', $planned->uuid)
            ->has('tasks.in_progress', 1)
            ->where('tasks.in_progress.0.id', $inProgress->uuid)
            ->has('tasks.blocked', 1)
            ->where('tasks.blocked.0.id', $blocked->uuid)
            ->missing('content')
            ->missing('source'));
});

test('roadmap lanes use planned queue order and workflow timestamps', function (): void {
    $manager = developmentRoadmapUser(
        DevelopmentPermission::DevelopmentRoadmapManage,
        'Roadmap Lane Order Manager',
    );
    $secondPlanned = DevelopmentTask::factory()->planned()->create(['roadmap_position' => 2]);
    $firstPlanned = DevelopmentTask::factory()->planned()->create(['roadmap_position' => 1]);
    $newerInProgress = DevelopmentTask::factory()->inProgress()->create([
        'roadmap_position' => null,
        'started_at' => now()->subHour(),
    ]);
    $olderInProgress = DevelopmentTask::factory()->inProgress()->create([
        'roadmap_position' => null,
        'started_at' => now()->subHours(2),
    ]);
    $newerBlocked = DevelopmentTask::factory()->blocked()->create([
        'roadmap_position' => null,
        'blocked_at' => now()->subHour(),
    ]);
    $olderBlocked = DevelopmentTask::factory()->blocked()->create([
        'roadmap_position' => null,
        'blocked_at' => now()->subHours(2),
    ]);

    $this->actingAs($manager)
        ->get(route('development.roadmap.index'))
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('tasks.planned.0.id', $firstPlanned->uuid)
            ->where('tasks.planned.1.id', $secondPlanned->uuid)
            ->where('tasks.in_progress.0.id', $olderInProgress->uuid)
            ->where('tasks.in_progress.1.id', $newerInProgress->uuid)
            ->where('tasks.blocked.0.id', $olderBlocked->uuid)
            ->where('tasks.blocked.1.id', $newerBlocked->uuid));
});

test('roadmap metadata updates are authorized validated and attributed', function (): void {
    $manager = developmentRoadmapUser(
        DevelopmentPermission::DevelopmentRoadmapManage,
        'Roadmap Metadata Manager',
    );
    $requester = developmentRoadmapUser(
        DevelopmentPermission::DevelopmentRequestsCreate,
        'Roadmap Metadata Requester',
    );
    $assignee = User::factory()->create(['name' => 'Assigned Developer']);
    $firstTask = DevelopmentTask::factory()->planned()->create(['roadmap_position' => 1]);
    $secondTask = DevelopmentTask::factory()->planned()->create(['roadmap_position' => 2]);
    $task = DevelopmentTask::factory()->planned()->create(['roadmap_position' => 3]);

    $this->actingAs($manager)
        ->patch(route('development.tasks.roadmap.update', $task), [
            'priority' => TaskPriority::High->value,
            'assignee_id' => $assignee->uuid,
            'roadmap_position' => 1,
        ])
        ->assertRedirect()
        ->assertSessionHas('success', 'Roadmap details updated.');

    $task->refresh();
    $activities = Activity::query()
        ->where('subject_type', DevelopmentTask::class)
        ->where('subject_id', $task->getKey())
        ->where('event', ActivityEvent::Updated)
        ->latest('id')
        ->get();
    $changedAttributes = $activities
        ->flatMap(fn (Activity $activity): array => array_keys(
            $activity->attribute_changes?->get('attributes', []) ?? [],
        ))
        ->unique()
        ->values()
        ->all();

    expect($task->priority)->toBe(TaskPriority::High)
        ->and($task->assignee?->is($assignee))->toBeTrue()
        ->and($task->roadmap_position)->toBe(1)
        ->and($firstTask->refresh()->roadmap_position)->toBe(2)
        ->and($secondTask->refresh()->roadmap_position)->toBe(3)
        ->and($activities->every(fn (Activity $activity): bool => $activity->causer?->is($manager) === true))->toBeTrue()
        ->and($changedAttributes)->toContain(
            'priority',
            'assignee_id',
            'roadmap_position',
        );

    $this->actingAs($requester)
        ->patch(route('development.tasks.roadmap.update', $task), [
            'priority' => TaskPriority::Low->value,
            'assignee_id' => null,
            'roadmap_position' => 1,
        ])
        ->assertForbidden();

    expect($task->refresh()->priority)->toBe(TaskPriority::High)
        ->and($task->assignee?->is($assignee))->toBeTrue();
});

test('queue positions are required only for planned tasks', function (): void {
    $manager = developmentRoadmapUser(
        DevelopmentPermission::DevelopmentRoadmapManage,
        'Roadmap Queue Validation Manager',
    );
    $planned = DevelopmentTask::factory()->planned()->create(['roadmap_position' => 1]);
    $inProgress = DevelopmentTask::factory()->inProgress()->create(['roadmap_position' => null]);

    $this->actingAs($manager)
        ->patch(route('development.tasks.roadmap.update', $planned), [
            'priority' => TaskPriority::Normal->value,
            'assignee_id' => null,
        ])
        ->assertSessionHasErrors('roadmap_position');

    $this->actingAs($manager)
        ->patch(route('development.tasks.roadmap.update', $inProgress), [
            'priority' => TaskPriority::Normal->value,
            'assignee_id' => null,
            'roadmap_position' => 1,
        ])
        ->assertSessionHasErrors('roadmap_position');
});

test('roadmap metadata rejects inactive assignees and non-roadmap tasks', function (): void {
    $manager = developmentRoadmapUser(
        DevelopmentPermission::DevelopmentRoadmapManage,
        'Roadmap Validation Manager',
    );
    $inactiveAssignee = User::factory()->create(['is_active' => false]);
    $planned = DevelopmentTask::factory()->planned()->create();
    $submitted = DevelopmentTask::factory()->create();

    $this->actingAs($manager)
        ->patch(route('development.tasks.roadmap.update', $planned), [
            'priority' => TaskPriority::Normal->value,
            'assignee_id' => $inactiveAssignee->uuid,
            'roadmap_position' => 1,
        ])
        ->assertSessionHasErrors('assignee_id');

    $this->actingAs($manager)
        ->patch(route('development.tasks.roadmap.update', $submitted), [
            'priority' => TaskPriority::Normal->value,
            'assignee_id' => null,
            'roadmap_position' => 1,
        ])
        ->assertSessionHasErrors('status');
});

test('assignees and managers move assigned work through roadmap progress states', function (): void {
    $manager = developmentRoadmapUser(
        DevelopmentPermission::DevelopmentRoadmapManage,
        'Roadmap Progress Manager',
    );
    $assignee = User::factory()->create();
    $otherUser = User::factory()->create();
    $firstQueuedTask = DevelopmentTask::factory()->planned()->create(['roadmap_position' => 1]);
    $task = DevelopmentTask::factory()
        ->planned()
        ->for($assignee, 'assignee')
        ->create(['roadmap_position' => 2]);
    $lastQueuedTask = DevelopmentTask::factory()->planned()->create(['roadmap_position' => 3]);

    $this->actingAs($assignee)
        ->patch(route('development.tasks.progress.update', $task), [
            'status' => DevelopmentTaskStatus::InProgress->value,
        ])
        ->assertRedirect();

    $startedAt = $task->refresh()->started_at;

    expect($task->roadmap_position)->toBeNull()
        ->and($firstQueuedTask->refresh()->roadmap_position)->toBe(1)
        ->and($lastQueuedTask->refresh()->roadmap_position)->toBe(2);

    $this->actingAs($otherUser)
        ->patch(route('development.tasks.progress.update', $task), [
            'status' => DevelopmentTaskStatus::Blocked->value,
        ])
        ->assertForbidden();

    $this->actingAs($manager)
        ->patch(route('development.tasks.progress.update', $task), [
            'status' => DevelopmentTaskStatus::Blocked->value,
        ])
        ->assertRedirect();

    $this->travel(10)->minutes();

    $this->actingAs($assignee)
        ->patch(route('development.tasks.progress.update', $task), [
            'status' => DevelopmentTaskStatus::InProgress->value,
        ])
        ->assertRedirect();

    expect($task->refresh()->status)->toBe(DevelopmentTaskStatus::InProgress)
        ->and($task->blocked_at)->not->toBeNull()
        ->and($task->started_at)->toEqual($startedAt);
});

test('work cannot start until it has an assignee', function (): void {
    $manager = developmentRoadmapUser(
        DevelopmentPermission::DevelopmentRoadmapManage,
        'Roadmap Assignment Manager',
    );
    $task = DevelopmentTask::factory()->planned()->create(['assignee_id' => null]);

    $this->actingAs($manager)
        ->patch(route('development.tasks.progress.update', $task), [
            'status' => DevelopmentTaskStatus::InProgress->value,
        ])
        ->assertSessionHasErrors('assignee_id');

    expect($task->refresh()->status)->toBe(DevelopmentTaskStatus::Planned)
        ->and($task->started_at)->toBeNull();
});

test('assignees attach validated task links that remain visible on the shared task', function (): void {
    $requester = developmentRoadmapUser(
        DevelopmentPermission::DevelopmentRequestsCreate,
        'Roadmap Link Requester',
    );
    $assignee = User::factory()->create();
    $task = DevelopmentTask::factory()
        ->planned()
        ->for($requester, 'submitter')
        ->for($assignee, 'assignee')
        ->create();

    $this->actingAs($assignee)
        ->post(route('development.tasks.links.store', $task), [
            'type' => DevelopmentTaskLinkType::PullRequest->value,
            'label' => 'Implementation PR',
            'url' => 'https://github.com/southeast-code/app/pull/42',
        ])
        ->assertRedirect()
        ->assertSessionHas('success', 'Task link attached.');

    $link = DevelopmentTaskLink::query()->sole();

    expect($link->task->is($task))->toBeTrue()
        ->and($link->addedBy?->is($assignee))->toBeTrue()
        ->and($link->type)->toBe(DevelopmentTaskLinkType::PullRequest);

    $this->actingAs($requester)
        ->get(route('development.tasks.show', $task))
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Development/Tasks/Show/Page')
            ->has('task.links', 1)
            ->where('task.links.0.id', $link->uuid)
            ->where('task.links.0.label', 'Implementation PR')
            ->where('task.can.attach_link', false));

    $this->actingAs($assignee)
        ->post(route('development.tasks.links.store', $task), [
            'type' => DevelopmentTaskLinkType::External->value,
            'url' => 'javascript:alert(1)',
        ])
        ->assertSessionHasErrors('url');

    expect($task->links()->count())->toBe(1);
});
