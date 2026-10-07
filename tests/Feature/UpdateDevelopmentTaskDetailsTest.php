<?php

declare(strict_types=1);

use App\Actions\UpdateDevelopmentTaskDetails;
use App\Enums\DevelopmentRequestType;
use App\Enums\DevelopmentTaskArea;
use App\Enums\DevelopmentTaskStatus;
use App\Enums\TaskPriority;
use App\Models\DevelopmentTask;
use App\Models\User;
use Illuminate\Validation\ValidationException;

test('scope updates preserve task identity, ownership and roadmap lifecycle', function (): void {
    $actor = User::factory()->create();
    $task = DevelopmentTask::factory()->planned()->create([
        'roadmap_position' => 6,
        'priority' => TaskPriority::High,
        'assignee_id' => $actor->getKey(),
    ]);
    $uuid = $task->uuid;
    $submitterId = $task->submitter_id;

    $updated = app(UpdateDevelopmentTaskDetails::class)->handleRaw($task, [
        'title' => '  Extract the component gallery  ',
        'request_type' => DevelopmentRequestType::Feature->value,
        'affected_area' => DevelopmentTaskArea::Development->value,
        'description' => '  Preserve the gallery for future package work.  ',
        'desired_outcome' => '  The application runs independently of the gallery.  ',
        'acceptance_criteria' => '  Gallery routes and runtime dependencies are disconnected.  ',
        'priority' => TaskPriority::Low->value,
    ], $actor);

    $this->assertDatabaseHas('development_tasks', [
        'uuid' => $uuid,
        'title' => 'Extract the component gallery',
        'description' => 'Preserve the gallery for future package work.',
        'desired_outcome' => 'The application runs independently of the gallery.',
        'acceptance_criteria' => 'Gallery routes and runtime dependencies are disconnected.',
        'status' => DevelopmentTaskStatus::Planned->value,
        'priority' => TaskPriority::High->value,
        'roadmap_position' => 6,
        'assignee_id' => $actor->getKey(),
        'submitter_id' => $submitterId,
    ]);

    expect($updated->is($task))->toBeTrue();
});

test('invalid scope updates do not change the task', function (): void {
    $actor = User::factory()->create();
    $task = DevelopmentTask::factory()->planned()->create(['title' => 'Keep the existing scope']);

    expect(fn (): DevelopmentTask => app(UpdateDevelopmentTaskDetails::class)->handleRaw($task, [
        'title' => '   ',
        'request_type' => 'unknown',
        'affected_area' => DevelopmentTaskArea::Development->value,
        'description' => 'A proposed replacement.',
        'desired_outcome' => 'A new outcome.',
        'acceptance_criteria' => 'New criteria.',
    ], $actor))->toThrow(ValidationException::class);

    $this->assertDatabaseHas('development_tasks', [
        'uuid' => $task->uuid,
        'title' => 'Keep the existing scope',
    ]);
});

test('terminal and archived tasks cannot have their scope rewritten', function (DevelopmentTaskStatus $status, bool $archived): void {
    $actor = User::factory()->create();
    $task = DevelopmentTask::factory()->create([
        'title' => 'Preserve the recorded scope',
        'status' => $status,
        'deleted_at' => $archived ? now() : null,
    ]);

    expect(fn (): DevelopmentTask => app(UpdateDevelopmentTaskDetails::class)->handleRaw($task, [
        'title' => 'A replacement scope',
        'request_type' => DevelopmentRequestType::Feature->value,
        'affected_area' => DevelopmentTaskArea::Development->value,
        'description' => 'A proposed replacement.',
        'desired_outcome' => 'A new outcome.',
        'acceptance_criteria' => 'New criteria.',
    ], $actor))->toThrow(ValidationException::class);

    $this->assertDatabaseHas('development_tasks', [
        'uuid' => $task->uuid,
        'title' => 'Preserve the recorded scope',
    ]);
})->with([
    'completed' => [DevelopmentTaskStatus::Completed, false],
    'cancelled' => [DevelopmentTaskStatus::Cancelled, false],
    'rejected' => [DevelopmentTaskStatus::Rejected, false],
    'archived' => [DevelopmentTaskStatus::Planned, true],
]);
