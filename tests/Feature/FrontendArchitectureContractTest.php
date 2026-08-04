<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

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

test('the authenticated workspace uses one client and server layout boundary', function (): void {
    $appEntry = File::get(resource_path('js/app.ts'));
    $serverEntry = File::get(resource_path('js/ssr.ts'));
    $layoutResolver = File::get(resource_path('js/layouts/resolveAppLayout.ts'));
    $defaultLayout = File::get(resource_path('js/layouts/Default.vue'));
    $workspaceSidebar = File::get(resource_path('js/components/WorkspaceSidebar.vue'));
    $workspaceSearch = File::get(resource_path('js/components/WorkspaceSearch.vue'));

    $pagesDeclaringDefaultLayout = collect(File::allFiles(resource_path('js/pages')))
        ->filter(fn (SplFileInfo $page): bool => Str::contains(
            File::get($page->getPathname()),
            '@/layouts/Default.vue',
        ))
        ->map(fn (SplFileInfo $page): string => $page->getRelativePathname())
        ->values()
        ->all();

    expect($appEntry)
        ->toContain('import { resolveAppLayout } from "@/layouts/resolveAppLayout";')
        ->toContain('layout: resolveAppLayout,')
        ->and($serverEntry)
        ->toContain('import { resolveAppLayout } from "@/layouts/resolveAppLayout";')
        ->toContain('layout: resolveAppLayout,')
        ->and($layoutResolver)
        ->toContain('export function resolveAppLayout(name: string, _page: Page): Component | null')
        ->toContain('name.startsWith("auth/")')
        ->and($pagesDeclaringDefaultLayout)->toBe([])
        ->and($defaultLayout)
        ->toContain('<WorkspaceSidebar')
        ->toContain('useWorkspaceNavigation')
        ->not->toContain('@/routes/')
        ->and($workspaceSidebar)
        ->toContain('aria-label="Primary workspace navigation"')
        ->toContain('aria-label="Account navigation"')
        ->toContain('<UserMenu :collapsed="collapsed" />')
        ->and($workspaceSearch)
        ->toContain('aria-label", "Workspace navigation search results"');
});
