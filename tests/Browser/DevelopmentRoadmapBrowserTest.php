<?php

declare(strict_types=1);

use App\Enums\RoleName;
use App\Enums\TaskPriority;
use App\Models\DevelopmentTask;
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
