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
        ->resize(1440, 1000)
        ->assertVisible('[data-testid="development-task-summary"]')
        ->assertVisible('[data-testid="development-task-brief"]')
        ->assertVisible('[data-testid="development-task-workflow"]')
        ->assertSee('Request brief')
        ->assertSee('Move this work forward')
        ->assertSee('Roadmap details')
        ->assertSee('Implementation progress')
        ->assertSee('Task links')
        ->assertSee('Start work')
        ->assertScript('getComputedStyle(document.querySelector(\'[data-testid="development-task-layout"]\')).gridTemplateColumns.split(" ").length', 2)
        ->resize(375, 812)
        ->assertScript('getComputedStyle(document.querySelector(\'[data-testid="development-task-layout"]\')).gridTemplateColumns.split(" ").length', 1)
        ->assertScript('document.documentElement.scrollWidth <= window.innerWidth', true)
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs()
        ->assertNoAccessibilityIssues();
});

test('task link placeholders do not overlap their floating labels', function (): void {
    app(RolePermissionSeeder::class)->run();

    $manager = User::factory()->create();
    $manager->assignRole(RoleName::Admin->value);
    $task = DevelopmentTask::factory()
        ->planned()
        ->for($manager, 'assignee')
        ->create();

    $this->actingAs($manager);

    visit(route('development.tasks.show', $task, false))
        ->inDarkMode()
        ->assertSee('Task links')
        ->assertScript('document.querySelector(\'.form-field__shell:has(input[name="label"]) .form-field__label\')?.getBoundingClientRect().top < document.querySelector(\'.form-field__shell:has(input[name="label"])\')?.getBoundingClientRect().top + 8', true)
        ->assertScript('document.querySelector(\'.form-field__shell:has(input[name="url"]) .form-field__label\')?.getBoundingClientRect().top < document.querySelector(\'.form-field__shell:has(input[name="url"])\')?.getBoundingClientRect().top + 8', true)
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
