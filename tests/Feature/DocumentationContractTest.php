<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

test('the database workflow replaces markdown task queues', function (): void {
    $workflow = File::get(base_path('docs/development/workflow.md'));
    $skill = File::get(base_path('.agents/skills/user-testing-roadmap/SKILL.md'));

    expect($workflow)
        ->toContain('sole source of truth')
        ->toContain('/development/requests/create')
        ->toContain('/development/review')
        ->toContain('/development/roadmap')
        ->toContain('/development/user-testing')
        ->and($skill)
        ->toContain('development_tasks')
        ->toContain('RequestDevelopmentTaskTesting')
        ->toContain('RecordDevelopmentTestResult');

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
        ->push(File::get(base_path('README.md')))
        ->implode("\n");

    foreach (['active-roadmap.md', 'active-decision-points.md', 'docs/development/active-tasks'] as $legacyReference) {
        expect($maintainedDocumentation)->not->toContain($legacyReference);
    }
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
