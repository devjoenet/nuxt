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
        ->toContain('Route pages use `<script setup>` so component state remains instance-scoped')
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

test('retired frontend files and duplicated pure helpers cannot return', function (): void {
    foreach ([
        'resources/js/layouts/AuthBasic.vue',
        'resources/js/layouts/Auth/AuthSplitLayout.vue',
        'resources/js/pages/Auth/ForgotPasswordNuxt.vue',
        'resources/js/composables/getErrors.ts',
        'resources/js/composables/useInitials.ts',
    ] as $retiredPath) {
        expect(File::exists(base_path($retiredPath)))->toBeFalse("Retired frontend file returned: {$retiredPath}");
    }

    $vueFilesWithInlineDateFormatters = collect(File::allFiles(resource_path('js')))
        ->filter(fn (SplFileInfo $file): bool => $file->getExtension() === 'vue')
        ->filter(fn (SplFileInfo $file): bool => Str::contains(File::get($file->getPathname()), 'new Intl.DateTimeFormat'))
        ->map(fn (SplFileInfo $file): string => $file->getRelativePathname())
        ->values()
        ->all();

    expect($vueFilesWithInlineDateFormatters)->toBe([])
        ->and(File::exists(resource_path('js/lib/dateFormatters.ts')))->toBeTrue()
        ->and(File::exists(resource_path('js/lib/nameFormatters.ts')))->toBeTrue()
        ->and(File::exists(resource_path('js/lib/formErrors.ts')))->toBeTrue()
        ->and(File::exists(resource_path('js/lib/webUrl.ts')))->toBeTrue();
});

test('route pages keep mutable state instance scoped', function (): void {
    $pageFilesWithModuleScripts = collect(File::allFiles(resource_path('js/pages')))
        ->filter(fn (SplFileInfo $file): bool => $file->getExtension() === 'vue')
        ->filter(fn (SplFileInfo $file): bool => preg_match(
            '/<script(?=[\s>])(?![^>]*\bsetup\b)[^>]*>/',
            File::get($file->getPathname()),
        ) === 1)
        ->map(fn (SplFileInfo $file): string => $file->getRelativePathname())
        ->values()
        ->all();

    expect($pageFilesWithModuleScripts)->toBe([]);
});

test('eslint enforces frontend dependency direction without banning direct Nuxt UI composition', function (): void {
    $eslintConfig = File::get(base_path('eslint.config.ts'));

    expect($eslintConfig)
        ->toContain('"no-restricted-imports"')
        ->toContain('Product primitives and composables cannot depend on workflow or route rendering layers.')
        ->toContain('Pure utilities cannot depend on frontend rendering layers.')
        ->toContain('Pure utilities cannot depend on Vue lifecycle, VueUse, or Inertia.')
        ->toContain('Domain sections cannot depend on layouts or route pages.')
        ->toContain('Workflow shells cannot depend on route pages or domain sections.')
        ->not->toContain('Direct Nuxt UI imports are forbidden');
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
            ->toContain('@nuxt/ui/components/Button.vue')
            ->not->toContain('resolveComponent')
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

test('authenticated editors share form composition without replacing typed fields', function (): void {
    foreach (['WorkspaceFormPage.vue', 'FormSectionHeading.vue', 'FormActions.vue'] as $component) {
        expect(File::exists(resource_path("js/components/{$component}")))
            ->toBeTrue("Missing form composition component: {$component}");
    }

    foreach ([
        'pages/Crm/Contacts/Form.vue',
        'pages/Crm/Leads/Form.vue',
        'pages/Crm/Organizations/Form.vue',
        'pages/Development/Requests/Create.vue',
    ] as $formPage) {
        expect(File::get(resource_path("js/{$formPage}")))
            ->toContain('<WorkspaceFormPage')
            ->not->toContain('<Form ');
    }

    $workspaceFormPage = File::get(resource_path('js/components/WorkspaceFormPage.vue'));
    $profilePage = File::get(resource_path('js/pages/Settings/Profile.vue'));

    expect($workspaceFormPage)
        ->toContain('disable-while-processing')
        ->toContain('data-testid="form-error-summary"')
        ->toContain('<FormSectionHeading')
        ->toContain('<FormActions')
        ->and(preg_match_all('/<UForm(?:\s|>)/', $profilePage))
        ->toBe(1)
        ->and($profilePage)
        ->toContain('onSuccess: () =>');
});

test('crm details share typed lifecycle presentation and accessible confirmation', function (): void {
    $defaultLayout = File::get(resource_path('js/layouts/Default.vue'));
    $flashToasts = File::get(resource_path('js/composables/useFlashToasts.ts'));
    $confirmation = File::get(resource_path('js/components/ConfirmActionButton.vue'));

    expect($defaultLayout)
        ->toContain('useFlashToasts();')
        ->and($flashToasts)
        ->toContain('lastToast')
        ->toContain('watch(() => page.props.flash.success')
        ->and($confirmation)
        ->toContain('<UModal')
        ->toContain('<UButton color="neutral" label="Cancel"');

    foreach (['Contacts', 'Organizations', 'Leads'] as $domain) {
        expect(File::get(resource_path("js/pages/Crm/{$domain}/Show.vue")))
            ->toContain('<CrmDetailLayout')
            ->not->toContain('window.confirm');
    }

    expect(File::get(resource_path('js/sections/Crm/Leads/LeadOverviewSection.vue')))
        ->toContain('<CrmDetailMetadata')
        ->toContain('<ConfirmActionButton')
        ->not->toContain('window.confirm');
});

test('complex workflows keep state inside focused page scoped boundaries', function (): void {
    $twoFactorState = File::get(resource_path('js/composables/useTwoFactorAuth.ts'));
    $landingHeroAssets = File::get(resource_path('js/composables/useLandingHeroAssets.ts'));
    $securityPage = File::get(resource_path('js/pages/Settings/Security.vue'));
    $landingPage = File::get(resource_path('js/pages/Welcome.vue'));
    $landingHeader = File::get(resource_path('js/components/Landing/LandingHeader.vue'));
    $landingContact = File::get(resource_path('js/components/Landing/LandingContactForm.vue'));

    expect($twoFactorState)
        ->toContain('provideTwoFactorAuth')
        ->toContain('inject(twoFactorAuthKey)')
        ->toContain('useHttp<Record<string, never>')
        ->not->toContain('fetch(')
        ->and($securityPage)
        ->toContain('provideTwoFactorAuth();');

    foreach ([
        'TwoFactorSetupStep.vue',
        'TwoFactorConfirmationStep.vue',
        'TwoFactorRecoveryCodes.vue',
        'TwoFactorRegenerateRecoveryCodesForm.vue',
    ] as $twoFactorStep) {
        expect(File::exists(resource_path("js/components/{$twoFactorStep}")))
            ->toBeTrue("Missing two-factor step: {$twoFactorStep}");
    }

    expect($landingPage)
        ->toContain('useLandingHeroAssets()')
        ->not->toContain('HeroAssetSet')
        ->and($landingHeroAssets)
        ->not->toContain('useColorMode')
        ->not->toContain('useMounted')
        ->and($landingHeader)
        ->toContain('useLandingSectionNavigation()')
        ->and($landingContact)
        ->toContain('useLandingAttribution()');

    foreach ([
        'DevelopmentTaskRoadmapSummaryFields.vue',
        'DevelopmentTaskReviewActions.vue',
        'DevelopmentTaskProgressAction.vue',
        'DevelopmentTaskTestingRequestForm.vue',
    ] as $taskSection) {
        expect(File::exists(resource_path("js/sections/Development/Tasks/{$taskSection}")))
            ->toBeTrue("Missing Development task section: {$taskSection}");
    }
});

test('landing appearance stays stable across server rendering and first paint', function (): void {
    $blade = File::get(resource_path('views/app.blade.php'));
    $bootstrap = File::get(public_path('theme-bootstrap.js'));
    $picture = File::get(resource_path('js/components/Landing/LandingHeroPicture.vue'));
    $userMenu = File::get(resource_path('js/components/UserMenu.vue'));

    expect($blade)
        ->toContain('data-appearance=')
        ->toContain('<script src="/theme-bootstrap.js"></script>')
        ->and($bootstrap)
        ->toContain('vueuse-color-scheme')
        ->toContain('prefers-color-scheme: dark')
        ->toContain('classList.toggle("dark", isDark)')
        ->and($picture)
        ->toContain('(prefers-color-scheme: dark)')
        ->toContain('(prefers-color-scheme: light)')
        ->toContain('"not all"')
        ->and($userMenu)
        ->toContain('document.cookie = `appearance=${appearance}')
        ->toContain('preference === "auto" ? "system" : preference');
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
