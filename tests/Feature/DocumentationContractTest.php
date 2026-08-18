<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

test('the database workflow replaces markdown task queues', function (): void {
    $workflow = File::get(base_path('docs/development/workflow.md'));
    $skill = File::get(base_path('.agents/skills/user-testing-roadmap/SKILL.md'));
    $readme = File::get(base_path('README.md'));
    $completedRoadmap = File::get(base_path('docs/development/completed-roadmap.md'));

    expect($workflow)
        ->toContain('sole source of truth')
        ->toContain('/development/requests/create')
        ->toContain('/development/review')
        ->toContain('/development/roadmap')
        ->toContain('/development/user-testing')
        ->toContain('Each testing attempt is a distinct round')
        ->toContain('dedicated Development permissions')
        ->toContain('Pass the acting user explicitly')
        ->toContain('Keep lifecycle changes transactional')
        ->and($skill)
        ->toContain('development_tasks')
        ->toContain('SubmitDevelopmentTask')
        ->toContain('ReviewDevelopmentTask')
        ->toContain('PromoteDevelopmentTask')
        ->toContain('RequestDevelopmentTaskTesting')
        ->toContain('RecordDevelopmentTestResult')
        ->and($readme)
        ->toContain('[Development work procedure](docs/development/workflow.md)')
        ->and($completedRoadmap)
        ->toContain('historical context, not an active queue')
        ->toContain('must not receive new tasks');

    foreach ([
        'docs/development/active-roadmap.md',
        'docs/development/active-decision-points.md',
        'docs/development/active-tasks',
    ] as $legacyPath) {
        expect(File::exists(base_path($legacyPath)))->toBeFalse("Legacy workflow path still exists: {$legacyPath}");
    }

    $maintainedDocumentation = collect(File::allFiles(base_path('docs')))
        ->filter(fn (SplFileInfo $file): bool => $file->getExtension() === 'md')
        ->map(fn (SplFileInfo $file): string => File::get($file->getPathname()))
        ->push($skill)
        ->push($readme)
        ->implode("\n");

    foreach (['active-roadmap.md', 'active-decision-points.md', 'docs/development/active-tasks'] as $legacyReference) {
        expect($maintainedDocumentation)->not->toContain($legacyReference);
    }
});

test('the Argon frontend overhaul remains a reference blueprint instead of a task queue', function (): void {
    $blueprint = File::get(base_path('docs/development/argon-frontend-overhaul.md'));
    $readme = File::get(base_path('README.md'));

    expect($blueprint)
        ->toContain('durable reference blueprint,')
        ->toContain('not an active task queue')
        ->toContain('database-backed Development workspace remains the sole source of truth')
        ->toContain('resources/js/components/Ui')
        ->toContain('The existing `resources/js/sections` root will be removed progressively')
        ->toContain('Licensed Argon HTML examples | 69')
        ->toContain('Each numbered item is represented by one live Development task and one pull request')
        ->and($readme)
        ->toContain('[Argon frontend overhaul blueprint](docs/development/argon-frontend-overhaul.md)');
});

test('maintained deployment documentation only names registered artisan commands', function (): void {
    $commands = collect(File::allFiles(base_path('docs')))
        ->filter(fn (SplFileInfo $file): bool => $file->getExtension() === 'md')
        ->map(fn (SplFileInfo $file): string => File::get($file->getPathname()))
        ->implode("\n");
    preg_match_all('/php artisan ([a-z0-9:_-]+)/', $commands, $matches);

    $registered = array_keys(Artisan::all());

    foreach (array_unique($matches[1]) as $command) {
        expect($registered)->toContain($command);
    }
});
