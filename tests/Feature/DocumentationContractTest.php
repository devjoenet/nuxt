<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

test('every listed active task link resolves to a local markdown file', function (): void {
    $roadmapPath = base_path('docs/development/active-roadmap.md');
    $roadmap = File::get($roadmapPath);
    expect($roadmap)->toContain('## Active Development Tasks');

    preg_match_all('/\]\(([^)]+)\)/', $roadmap, $matches);

    $links = collect($matches[1])
        ->filter(fn (string $link): bool => str_contains($link, 'active-tasks/'))
        ->map(fn (string $link): string => Str::before($link, '#'))
        ->unique()
        ->values();

    foreach ($links as $link) {
        $path = realpath(dirname($roadmapPath).'/'.$link);

        expect($path)->not->toBeFalse("Missing active task link: {$link}");
        expect(File::extension((string) $path))->toBe('md');
    }
});

test('maintained deployment documentation only names registered artisan commands', function (): void {
    $commands = collect(File::allFiles(base_path('docs')))
        ->filter(fn (SplFileInfo $file): bool => $file->getExtension() === 'md')
        ->reject(fn (SplFileInfo $file): bool => str_contains($file->getPathname(), DIRECTORY_SEPARATOR.'active-tasks'.DIRECTORY_SEPARATOR))
        ->map(fn (SplFileInfo $file): string => File::get($file->getPathname()))
        ->implode("\n");
    preg_match_all('/php artisan ([a-z0-9:_-]+)/', $commands, $matches);

    $registered = array_keys(Artisan::all());

    foreach (array_unique($matches[1]) as $command) {
        expect($registered)->toContain($command);
    }
});
