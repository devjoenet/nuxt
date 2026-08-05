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
        ->toContain('Nested Vue feature directories')
        ->toContain('Authored TypeScript module')
        ->toContain('Static asset directories and filenames')
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

test('the frontend source follows the documented naming convention', function (): void {
    $vueArchitectureRoots = ['components', 'layouts', 'pages', 'sections'];
    $generatedRoots = ['actions/', 'routes/', 'wayfinder/'];

    $invalidVueDirectories = collect($vueArchitectureRoots)
        ->flatMap(function (string $root): array {
            return collect(File::allFiles(resource_path("js/{$root}")))
                ->flatMap(function (SplFileInfo $file) use ($root): array {
                    if ($file->getRelativePath() === '') {
                        return [];
                    }

                    $segments = explode(DIRECTORY_SEPARATOR, $file->getRelativePath());

                    return collect(array_keys($segments))
                        ->map(fn (int $index): string => $root.'/'.implode('/', array_slice($segments, 0, $index + 1)))
                        ->all();
                })
                ->all();
        })
        ->unique()
        ->reject(fn (string $directory): bool => preg_match('/^[A-Z][A-Za-z0-9]*$/', Str::afterLast($directory, '/')) === 1)
        ->sort()
        ->values()
        ->all();

    $frontendFiles = collect(File::allFiles(resource_path('js')));

    $invalidVueFiles = $frontendFiles
        ->filter(fn (SplFileInfo $file): bool => $file->getExtension() === 'vue')
        ->reject(fn (SplFileInfo $file): bool => preg_match('/^[A-Z][A-Za-z0-9]*\.vue$/', $file->getFilename()) === 1)
        ->map(fn (SplFileInfo $file): string => $file->getRelativePathname())
        ->sort()
        ->values()
        ->all();

    $invalidTypeScriptFiles = $frontendFiles
        ->filter(fn (SplFileInfo $file): bool => in_array($file->getExtension(), ['ts', 'tsx'], true))
        ->reject(fn (SplFileInfo $file): bool => Str::startsWith($file->getRelativePathname(), $generatedRoots))
        ->reject(fn (SplFileInfo $file): bool => preg_match('/^[a-z][A-Za-z0-9]*(?:\.d)?\.(?:ts|tsx)$/', $file->getFilename()) === 1)
        ->map(fn (SplFileInfo $file): string => $file->getRelativePathname())
        ->sort()
        ->values()
        ->all();

    $invalidAssetPaths = collect(File::allFiles(resource_path('js/assets')))
        ->reject(fn (SplFileInfo $file): bool => preg_match(
            '/^(?:[a-z0-9]+(?:-[a-z0-9]+)*\/)*[a-z0-9]+(?:-[a-z0-9]+)*(?:\.[a-z0-9]+)+$/',
            $file->getRelativePathname(),
        ) === 1)
        ->map(fn (SplFileInfo $file): string => $file->getRelativePathname())
        ->sort()
        ->values()
        ->all();

    expect($invalidVueDirectories)->toBe([])
        ->and($invalidVueFiles)->toBe([])
        ->and($invalidTypeScriptFiles)->toBe([])
        ->and($invalidAssetPaths)->toBe([]);
});

test('the current product primitives and Nuxt UI foundation remain explicit', function (): void {
    foreach ([
        'resources/js/components/FormField.vue',
        'resources/js/components/FormInput.vue',
        'resources/js/components/FormSelect.vue',
        'resources/js/components/FormMultiSelect.vue',
        'resources/js/components/FormTextarea.vue',
        'resources/js/components/AsyncCrmSelect.vue',
        'resources/js/components/WorkspacePage.vue',
        'resources/js/components/Crm/CrmDetailLayout.vue',
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

test('index workflows share navigation and framing without hiding domain columns', function (): void {
    $filterComposable = File::get(resource_path('js/composables/useInertiaFilters.ts'));
    $asyncCrmSelect = File::get(resource_path('js/components/AsyncCrmSelect.vue'));

    expect($filterComposable)
        ->toContain('watchDebounced(filters, visitFilters')
        ->toContain('onCancelToken: (token) =>')
        ->toContain('preservedQuery?: () => FilterState;')
        ->toContain('const reset = (): void =>')
        ->toContain('const visitPage = (page: number): void =>')
        ->not->toContain('setTimeout')
        ->and($asyncCrmSelect)
        ->toContain('useDebounceFn')
        ->not->toContain('setTimeout');

    foreach ([
        'IndexTableFrame.vue',
        'IndexEmptyState.vue',
        'IndexPagination.vue',
    ] as $component) {
        expect(File::exists(resource_path("js/components/{$component}")))
            ->toBeTrue("Missing index product component: {$component}");
    }

    foreach ([
        'Leads' => 'leadIndexColumns.ts',
        'Contacts' => 'contactIndexColumns.ts',
        'Organizations' => 'organizationIndexColumns.ts',
    ] as $domain => $columnFile) {
        $page = File::get(resource_path("js/pages/Crm/{$domain}/Index.vue"));
        $columns = File::get(resource_path("js/sections/Crm/{$domain}/{$columnFile}"));

        expect($page)
            ->toContain('useInertiaFilters({')
            ->toContain('<IndexTableFrame')
            ->toContain('<IndexEmptyState')
            ->toContain('<IndexPagination')
            ->toContain(':loading="isLoading"')
            ->not->toContain('setTimeout')
            ->and($columns)
            ->toContain('TableColumn<')
            ->toContain('id: "actions"');
    }

    foreach ([
        'pages/Admin/Audit/Index.vue',
        'sections/Admin/Users/AdminUsersTableSection.vue',
        'pages/Development/Requests/Index.vue',
        'pages/Development/Review/Index.vue',
    ] as $indexSurface) {
        expect(File::get(resource_path("js/{$indexSurface}")))
            ->toContain('<IndexPagination');
    }
});

test('authenticated pages compose the product workspace shell', function (): void {
    $workspacePage = File::get(resource_path('js/components/WorkspacePage.vue'));
    $crmDetailLayout = File::get(resource_path('js/components/Crm/CrmDetailLayout.vue'));
    $settingsLayout = File::get(resource_path('js/layouts/SettingsLayout.vue'));
    $workspaceShellOptOuts = [];

    $rawDashboardPanels = collect(File::allFiles(resource_path('js')))
        ->reject(fn (SplFileInfo $file): bool => $file->getRelativePathname() === 'components/WorkspacePage.vue')
        ->filter(fn (SplFileInfo $file): bool => Str::contains(File::get($file->getPathname()), '<UDashboardPanel'))
        ->map(fn (SplFileInfo $file): string => $file->getRelativePathname())
        ->values()
        ->all();

    expect($workspacePage)
        ->toContain('<Head :title="documentTitle ?? title" />')
        ->toContain('<UDashboardPanel')
        ->toContain('<UDashboardNavbar')
        ->toContain('<UDashboardSidebarCollapse />')
        ->toContain('<UDashboardToolbar v-if="$slots.toolbar"')
        ->toContain('<slot name="actions" />')
        ->toContain('data-workspace-page-content')
        ->toContain('type ContentWidth = "narrow" | "medium" | "wide" | "full";')
        ->and($crmDetailLayout)->toContain('<WorkspacePage')
        ->and($settingsLayout)->toContain('<WorkspacePage')
        ->and($rawDashboardPanels)->toBe($workspaceShellOptOuts);
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
        ->toContain('name.startsWith("Auth/")')
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
