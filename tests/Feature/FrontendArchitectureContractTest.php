<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

test('the frontend architecture defines the complete composition boundary', function (): void {
    $architecture = File::get(base_path('docs/development/frontend-ui-architecture.md'));
    $readme = File::get(base_path('README.md'));

    expect($architecture)
        ->toContain('Vendor foundation')
        ->toContain('Product primitives')
        ->toContain('Workflow shells')
        ->toContain('Domain sections')
        ->toContain('Pages')
        ->toContain('Composables')
        ->toContain('Pure utilities')
        ->toContain('Existing `FormField`, form controls, `AsyncCrmSelect`, and `CrmDetailLayout` are the starting point.')
        ->toContain('pass-through')
        ->toContain('wrappers that mirror Nuxt UI props and slots')
        ->toContain('## State ownership and event boundaries')
        ->toContain('## Props, events, slots, and styling')
        ->toContain('## Naming and typing')
        ->toContain('## Testing and enforcement')
        ->toContain('Responsibility beats line count.')
        ->toContain('New dependencies and new top-level folders under `resources/js` require separate approval.')
        ->and($readme)
        ->toContain('[frontend UI architecture](docs/development/frontend-ui-architecture.md)');
});

test('the frontend source uses only approved top-level homes', function (): void {
    $frontendDirectories = collect(File::directories(resource_path('js')))
        ->map(fn (string $directory): string => File::name($directory))
        ->sort()
        ->values()
        ->all();

    expect($frontendDirectories)->toBe([
        'actions',
        'assets',
        'components',
        'composables',
        'layouts',
        'lib',
        'pages',
        'routes',
        'sections',
        'types',
        'wayfinder',
    ])->and(File::isDirectory(resource_path('js/components/ui')))->toBeFalse();
});

test('the current product primitives and Nuxt UI foundation remain explicit', function (): void {
    foreach ([
        'resources/js/components/FormField.vue',
        'resources/js/components/FormInput.vue',
        'resources/js/components/FormSelect.vue',
        'resources/js/components/FormMultiSelect.vue',
        'resources/js/components/FormTextarea.vue',
        'resources/js/components/AsyncCrmSelect.vue',
        'resources/js/components/crm/CrmDetailLayout.vue',
        'resources/js/lib/utils.ts',
    ] as $foundationPath) {
        expect(File::exists(base_path($foundationPath)))->toBeTrue("Missing frontend foundation: {$foundationPath}");
    }

    $viteConfig = File::get(base_path('vite.config.ts'));

    expect($viteConfig)
        ->toContain('@nuxt/ui/vite')
        ->toContain('dirs: ["resources/js/components"]')
        ->toContain('theme: {')
        ->toContain('ui: {');
});
