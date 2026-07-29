<?php

declare(strict_types=1);

use App\Enums\DevelopmentTaskStatus;
use App\Enums\DevelopmentTestRunStatus;
use App\Enums\RoleName;
use App\Enums\TaskPriority;
use App\Models\DevelopmentTask;
use App\Models\DevelopmentTestRun;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

test('roadmap managers can navigate the database-backed board and task workspace', function (): void {
    app(RolePermissionSeeder::class)->run();

    $manager = User::factory()->create();
    $manager->assignRole(RoleName::Admin->value);
    $assignee = User::factory()->create(['name' => 'Roadmap Developer']);
    $task = DevelopmentTask::factory()
        ->planned()
        ->for($assignee, 'assignee')
        ->create([
            'title' => 'Track roadmap delivery',
            'priority' => TaskPriority::High,
            'roadmap_position' => 3,
        ]);

    $this->actingAs($manager);

    visit('/development/roadmap')
        ->assertSee('Development roadmap')
        ->assertSee('Planned')
        ->assertSee('Track roadmap delivery')
        ->assertSee('Position 3')
        ->assertSee('Roadmap Developer')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs()
        ->assertPresent('a[href="'.route('development.tasks.show', $task, false).'"]')
        ->navigate(route('development.tasks.show', $task, false))
        ->assertPathIs(route('development.tasks.show', $task, false))
        ->assertSee('Roadmap details')
        ->assertSee('Implementation progress')
        ->assertSee('Task links')
        ->assertSee('Start work')
        ->resize(375, 812)
        ->assertScript('document.documentElement.scrollWidth <= window.innerWidth', true)
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});

test('assigned testers can accept a task-specific round from the user-testing queue', function (): void {
    $tester = User::factory()->create();
    $task = DevelopmentTask::factory()->readyForTesting()->create([
        'title' => 'Verify task-specific testing',
        'acceptance_criteria' => 'The testing queue records a final accepted result.',
    ]);
    $testRun = DevelopmentTestRun::factory()
        ->for($task, 'task')
        ->for($tester, 'tester')
        ->create([
            'instructions' => 'Confirm the acceptance flow at desktop and mobile widths.',
            'build_reference' => 'build-42',
        ]);

    $this->actingAs($tester);

    visit('/development/user-testing')
        ->assertSee('Test the requested outcome, not a generic page checklist.')
        ->assertSee('Verify task-specific testing')
        ->assertSee('Confirm the acceptance flow at desktop and mobile widths.')
        ->assertSee('build-42')
        ->fill('feedback', 'The requested behavior passes.')
        ->pressAndWaitFor('Accept and complete')
        ->assertPathIs('/development/user-testing')
        ->assertSee('The testing queue is clear')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs()
        ->resize(375, 812)
        ->assertScript('document.documentElement.scrollWidth <= window.innerWidth', true)
        ->assertNoAccessibilityIssues();

    expect($testRun->refresh()->status)->toBe(DevelopmentTestRunStatus::Accepted)
        ->and(DevelopmentTask::query()->withTrashed()->findOrFail($task->getKey())->status)->toBe(DevelopmentTaskStatus::Completed);
});
