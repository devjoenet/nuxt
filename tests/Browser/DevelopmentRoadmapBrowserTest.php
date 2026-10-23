<?php

declare(strict_types=1);

use App\Enums\RoleName;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use SoutheastCode\DevelopmentWorkflow\Enums\DevelopmentTaskStatus;
use SoutheastCode\DevelopmentWorkflow\Enums\DevelopmentTestRunStatus;
use SoutheastCode\DevelopmentWorkflow\Enums\TaskPriority;
use SoutheastCode\DevelopmentWorkflow\Models\DevelopmentTask;
use SoutheastCode\DevelopmentWorkflow\Models\DevelopmentTestRun;

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
            'roadmap_position' => 1,
        ]);
    $lastPlannedTask = DevelopmentTask::factory()->planned()->create([
        'title' => 'Ship the next roadmap item',
        'roadmap_position' => 2,
    ]);

    $this->actingAs($manager);

    visit('/development/roadmap')
        ->assertSee('Development roadmap')
        ->assertSee('Planned')
        ->assertSee('Track roadmap delivery')
        ->assertSee('Planned 1')
        ->assertSee('Roadmap Developer')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs()
        ->assertPresent('[data-kanban-card="'.$task->uuid.'"]')
        ->click('[data-kanban-card="'.$task->uuid.'"]')
        ->assertPathIs(route('development.tasks.show', $task, false))
        ->resize(1440, 1000)
        ->assertVisible('[data-testid="development-task-summary"]')
        ->assertVisible('[data-testid="development-task-brief"]')
        ->assertVisible('[data-testid="development-task-workflow"]')
        ->assertSee('01 / Request brief')
        ->assertSee('Summary')
        ->assertSee('Desired outcome')
        ->assertSee('Acceptance criteria')
        ->assertSee('Task lifecycle')
        ->assertSee('The current phase is highlighted below.')
        ->assertMissing('h3#roadmap-details-heading')
        ->assertScript('document.querySelector(\'[data-testid="development-task-scroll-content"]\')?.closest(\'[data-slot="body"]\')?.scrollHeight > document.querySelector(\'[data-testid="development-task-scroll-content"]\')?.closest(\'[data-slot="body"]\')?.clientHeight', true)
        ->assertPresent('button[aria-label="Edit assignee"]')
        ->assertPresent('button[aria-label="Edit priority"]')
        ->assertPresent('button[aria-label="Edit roadmap position"]')
        ->assertScript('document.querySelector(\'[data-current="true"]\')?.textContent.includes("Planned")', true)
        ->assertSee('Implementation progress')
        ->assertSee('Task links')
        ->assertSee('Start work')
        ->click('button[aria-label="Edit assignee"]')
        ->assertPresent('button[aria-label="Assignee"]')
        ->assertSee('Save')
        ->click('button[aria-label="Cancel assignee edit"]')
        ->click('button[aria-label="Edit priority"]')
        ->assertPresent('button[aria-label="Priority"]')
        ->assertSee('Save')
        ->click('button[aria-label="Cancel priority edit"]')
        ->click('button[aria-label="Edit roadmap position"]')
        ->assertSee('Move to start')
        ->assertSee('Move to end')
        ->resize(375, 812)
        ->assertScript('document.documentElement.scrollWidth <= window.innerWidth', true)
        ->pressAndWaitFor('Move to end')
        ->assertSee('#2')
        ->resize(1440, 1000)
        ->assertScript('getComputedStyle(document.querySelector(\'[data-testid="development-task-layout"]\')).gridTemplateColumns.split(" ").length', 2)
        ->resize(375, 812)
        ->assertScript('getComputedStyle(document.querySelector(\'[data-testid="development-task-layout"]\')).gridTemplateColumns.split(" ").length', 1)
        ->assertScript('document.documentElement.scrollWidth <= window.innerWidth', true)
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs()
        ->assertNoAccessibilityIssues();

    expect($task->refresh()->roadmap_position)->toBe(2)
        ->and($lastPlannedTask->refresh()->roadmap_position)->toBe(1);
});

test('roadmap promotion renders nullable assignment controls without JavaScript errors', function (): void {
    app(RolePermissionSeeder::class)->run();

    $manager = User::factory()->create();
    $manager->assignRole(RoleName::Admin->value);
    $task = DevelopmentTask::factory()
        ->for($manager, 'submitter')
        ->for($manager, 'reviewer')
        ->underReview()
        ->create();

    $this->actingAs($manager);

    visit(route('development.tasks.show', $task, false))
        ->assertSee('Add to roadmap')
        ->pressAndWaitFor('Add to roadmap')
        ->assertSee('Unassigned')
        ->assertSee('Task lifecycle')
        ->assertPresent('button[aria-label="Edit assignee"]')
        ->assertPresent('button[aria-label="Edit priority"]')
        ->assertPresent('button[aria-label="Edit roadmap position"]')
        ->assertMissing('h3#roadmap-details-heading')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();

    expect($task->refresh()->status)->toBe(DevelopmentTaskStatus::Planned)
        ->and($task->assignee_id)->toBeNull()
        ->and($task->roadmap_position)->toBe(1);
});

test('roadmap task summaries remain read only without roadmap management permission', function (): void {
    $submitter = User::factory()->create();
    $task = DevelopmentTask::factory()
        ->planned()
        ->for($submitter, 'submitter')
        ->create([
            'priority' => TaskPriority::Normal,
            'roadmap_position' => 1,
        ]);

    $this->actingAs($submitter);

    visit(route('development.tasks.show', $task, false))
        ->assertVisible('[data-testid="development-task-summary"]')
        ->assertSee('Assignee')
        ->assertSee('Unassigned')
        ->assertSee('Priority')
        ->assertSee('Normal')
        ->assertSee('Roadmap position')
        ->assertSee('#1')
        ->assertSee('Task lifecycle')
        ->assertMissing('button[aria-label="Edit assignee"]')
        ->assertMissing('button[aria-label="Edit priority"]')
        ->assertMissing('button[aria-label="Edit roadmap position"]')
        ->assertMissing('h3#roadmap-details-heading')
        ->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
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

test('roadmap cards can be dragged to an empty column and persist after reload', function (): void {
    app(RolePermissionSeeder::class)->run();
    $manager = User::factory()->create()->assignRole(RoleName::Admin->value);
    $task = DevelopmentTask::factory()->planned()->for($manager, 'assignee')->create(['title' => 'Drag this roadmap task']);
    $this->actingAs($manager);

    $card = '[data-kanban-card="'.$task->uuid.'"]';
    $destination = 'section:has(> h3:has-text("In progress"))';
    $page = visit('/development/roadmap');
    $page->assertPresent('[aria-label="Development roadmap"]');
    $page->script('window.roadmapNode = document.querySelector(\'[aria-label="Development roadmap"]\'); null;');
    $page->assertPresent($card)->assertPresent($destination)->drag($card, $destination)
        ->assertScript('document.querySelector(\''.$card.'\')?.closest("section")?.querySelector("h3")?.textContent.includes("In progress")', true)
        ->assertScript('window.roadmapNode === document.querySelector(\'[aria-label="Development roadmap"]\')', true)
        ->assertNoJavaScriptErrors();
    expect($task->refresh()->status)->toBe(DevelopmentTaskStatus::InProgress);
    $page->navigate('/development/roadmap')
        ->assertScript('document.querySelector(\''.$card.'\')?.closest("section")?.querySelector("h3")?.textContent.includes("In progress")', true);
});

test('rejected roadmap drops restore the card and explain the assignment requirement', function (): void {
    app(RolePermissionSeeder::class)->run();
    $manager = User::factory()->create()->assignRole(RoleName::Admin->value);
    $task = DevelopmentTask::factory()->planned()->create(['title' => 'Assign before moving', 'assignee_id' => null]);
    $this->actingAs($manager);
    $card = '[data-kanban-card="'.$task->uuid.'"]';

    visit('/development/roadmap')
        ->drag($card, 'section:has(> h3:has-text("In progress"))')
        ->assertSee('Assign this task before starting work.')
        ->assertScript('document.querySelector(\''.$card.'\')?.closest("section")?.querySelector("h3")?.textContent.includes("Planned")', true)
        ->assertNoJavaScriptErrors();
    expect($task->refresh()->status)->toBe(DevelopmentTaskStatus::Planned);
});
