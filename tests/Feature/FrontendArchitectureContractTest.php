<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

test('the frontend architecture defines the complete composition boundary', function (): void {
    $architecture = File::get(base_path('docs/development/frontend-ui-architecture.md'));
    $readme = File::get(base_path('README.md'));

    expect($architecture)
        ->toContain('Vendor foundation')
        ->toContain('UI primitives')
        ->toContain('Application components')
        ->toContain('Page-family components')
        ->toContain('Pages')
        ->toContain('Composables')
        ->toContain('Pure helpers')
        ->toContain('Floating form controls')
        ->toContain('live behind typed `components/Ui/<Control>` public APIs')
        ->toContain('## Component directories and public APIs')
        ->toContain('## Page colocation and legacy retirement')
        ->toContain('## State, props, events, slots, and styling')
        ->toContain('Route pages use `<script setup>` so mutable page state remains instance-scoped')
        ->toContain('## Naming, typing, and generated code')
        ->toContain('## SFC responsibility budgets')
        ->toContain('UI primitive: 40 lines.')
        ->toContain('Reusable application component, layout, or page-family component: 60 lines.')
        ->toContain('Inertia page: 40 lines.')
        ->toContain('## Testing and enforcement')
        ->toContain('New dependencies or new top-level folders under `resources/js` still require separate approval.')
        ->and($readme)
        ->toContain('[frontend UI architecture](docs/development/frontend-ui-architecture.md)');
});

test('retired frontend files and duplicated pure helpers cannot return', function (): void {
    foreach ([
        'resources/js/layouts/AuthBasic.vue',
        'resources/js/layouts/Auth/AuthSplitLayout.vue',
        'resources/js/pages/Auth/ForgotPasswordNuxt.vue',
        'resources/js/pages/Welcome/Components/MetricCard.vue',
        'resources/js/composables/getErrors.ts',
        'resources/js/composables/useInitials.ts',
    ] as $retiredPath) {
        expect(File::exists(base_path($retiredPath)))->toBeFalse("Retired frontend file returned: {$retiredPath}");
    }

    $vueFilesWithInlineDateFormatters = collect(frontendContractFiles())
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
    $pageFilesWithModuleScripts = collect([...File::allFiles(resource_path('js/pages')), ...File::allFiles(Composer\InstalledVersions::getInstallPath('southeastcode/user-management').'/resources/js/pages')])
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

test('Vite Plus enforces frontend dependency direction and component public APIs', function (): void {
    foreach (['AsyncCrmSelect/AsyncCrmSelect.vue', 'CrmDetailLayout/CrmDetailLayout.vue'] as $component) {
        expect(File::exists(Composer\InstalledVersions::getInstallPath('southeastcode/crm').'/resources/js/components/'.$component))->toBeTrue();
    }

    $viteConfig = File::get(base_path('vite.config.ts'));

    expect($viteConfig)
        ->toContain('"no-restricted-imports"')
        ->toContain('Import UI primitives through their component-directory index.ts public API.')
        ->toContain('Import application components through their component-directory index.ts public API.')
        ->toContain('UI primitives cannot depend on application, route, or generated transport layers.')
        ->toContain('Application components cannot depend on workflow or route rendering layers.')
        ->toContain('Composables cannot depend on frontend rendering layers.')
        ->toContain('Pure utilities cannot depend on rendering or generated transport layers.')
        ->toContain('Pure utilities cannot depend on Vue lifecycle, VueUse, or Inertia.')
        ->toContain('Workflow shells cannot depend on route pages.')
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
        'components',
        'composables',
        'layouts',
        'lib',
        'pages',
        'routes',
        'types',
        'wayfinder',
    ])->and(File::isDirectory(resource_path('js/components/Ui')))->toBeTrue()
        ->and(collect(File::directories(resource_path('js/components')))
            ->map(fn (string $directory): string => File::name($directory))
            ->contains('ui'))
        ->toBeFalse()
        ->and(File::exists(resource_path('js/components/Ui/README.md')))->toBeTrue();
});

test('reusable components use PascalCase directories and public index exports', function (): void {
    $componentRoot = resource_path('js/components');
    $uiRoot = resource_path('js/components/Ui');

    $componentDirectories = collect(File::directories($uiRoot))
        ->merge(
            collect(File::directories($componentRoot))
                ->reject(fn (string $directory): bool => File::name($directory) === 'Ui'),
        );

    foreach ($componentDirectories as $componentDirectory) {
        $componentName = File::name($componentDirectory);
        $componentFile = "{$componentDirectory}/{$componentName}.vue";
        $indexFile = "{$componentDirectory}/index.ts";

        expect($componentName)->toMatch('/^[A-Z][A-Za-z0-9]*$/')
            ->and(File::exists($componentFile))
            ->toBeTrue("Missing component entry: {$componentFile}")
            ->and(File::exists($indexFile))
            ->toBeTrue("Missing component public API: {$indexFile}");

        $componentExport = sprintf(
            '/export\s+\{\s*default(?:\s+as\s+[A-Za-z][A-Za-z0-9]*)?\s*}\s+from\s+["\']\.\/%s\.vue["\']/',
            preg_quote($componentName, '/'),
        );

        expect(File::get($indexFile))->toMatch($componentExport);
    }

    $looseComponentFiles = collect(File::files($componentRoot))
        ->filter(fn (SplFileInfo $file): bool => $file->getExtension() === 'vue')
        ->map(fn (SplFileInfo $file): string => $file->getFilename())
        ->sort()
        ->values()
        ->all();

    expect($looseComponentFiles)->toBe([]);
});

test('callers cannot deep import component-directory internals', function (): void {
    $applicationComponentDirectories = collect(File::directories(resource_path('js/components')))
        ->map(fn (string $directory): string => File::name($directory))
        ->reject(fn (string $directory): bool => $directory === 'Ui')
        ->values();

    $deepImports = collect(frontendContractFiles())
        ->filter(fn (SplFileInfo $file): bool => in_array($file->getExtension(), ['ts', 'tsx', 'vue'], true))
        ->reject(fn (SplFileInfo $file): bool => Str::startsWith($file->getRelativePathname(), [
            'actions/',
            'routes/',
            'wayfinder/',
        ]))
        ->flatMap(function (SplFileInfo $file) use ($applicationComponentDirectories): array {
            preg_match_all('/(?:from\s+|import\()\s*["\']([^"\']+)["\']/', File::get($file->getPathname()), $matches);

            return collect($matches[1])
                ->filter(function (string $import) use ($applicationComponentDirectories): bool {
                    if (preg_match('#^@/components/Ui/[^/]+/.+#', $import) === 1) {
                        return true;
                    }

                    return $applicationComponentDirectories->contains(
                        fn (string $component): bool => preg_match(
                            '#^@/components/'.preg_quote($component, '#').'/'.preg_quote($component, '#').'(?:\.vue|/)#',
                            $import,
                        ) === 1,
                    );
                })
                ->map(fn (string $import): string => $file->getRelativePathname().': '.$import)
                ->all();
        })
        ->values()
        ->all();

    expect($deepImports)->toBe([]);
});

test('legacy sections are retired into page-family component homes', function (): void {
    $misnamedColocationDirectories = collect(File::directories(resource_path('js/pages')))
        ->flatMap(function (string $directory): array {
            return collect(File::allDirectories($directory))
                ->filter(fn (string $nestedDirectory): bool => Str::lower(File::name($nestedDirectory)) === 'components')
                ->reject(fn (string $nestedDirectory): bool => File::name($nestedDirectory) === 'Components')
                ->all();
        })
        ->values()
        ->all();

    expect(File::isDirectory(resource_path('js/sections')))->toBeFalse()
        ->and(File::exists(resource_path('js/pages/Welcome/Components/LandingContactSection.vue')))->toBeTrue()
        ->and(File::exists(Composer\InstalledVersions::getInstallPath('southeastcode/user-management').'/resources/js/pages/Admin/Users/Components/AdminUsersTableSection.vue'))->toBeTrue()
        ->and(File::exists(Composer\InstalledVersions::getInstallPath('southeastcode/crm').'/resources/js/pages/Leads/Components/LeadOverviewSection.vue'))->toBeTrue()
        ->and(File::exists(resource_path('js/pages/Development/Tasks/Components/DevelopmentTaskWorkflowSection.vue')))->toBeTrue()
        ->and($misnamedColocationDirectories)->toBe([]);
});

test('all baseline application pages are covered by the migrated route families', function (): void {
    $applicationPages = collect([...File::allFiles(resource_path('js/pages')), ...File::allFiles(Composer\InstalledVersions::getInstallPath('southeastcode/user-management').'/resources/js/pages')])
        ->filter(fn (SplFileInfo $file): bool => $file->getExtension() === 'vue')
        ->reject(fn (SplFileInfo $file): bool => Str::contains($file->getRelativePathname(), [
            '/Components/',
        ]))
        ->map(fn (SplFileInfo $file): string => $file->getRelativePathname())
        ->sort()
        ->values()
        ->all();

    expect($applicationPages)->toBe([
        'Admin/Dashboard.vue',
        'Admin/Jobs/Index.vue',
        'Admin/Roles/Index.vue',
        'Admin/Settings/Index.vue',
        'Admin/Users/Index.vue',
        'Auth/ConfirmPassword.vue',
        'Auth/ForgotPassword.vue',
        'Auth/Login.vue',
        'Auth/ResetPassword.vue',
        'Auth/TwoFactorChallenge.vue',
        'Auth/VerifyEmail.vue',
        'Dashboard.vue',
        'Development/Overview.vue',
        'Development/Requests/Create.vue',
        'Development/Requests/Index.vue',
        'Development/Review/Index.vue',
        'Development/Roadmap/Index.vue',
        'Development/Tasks/Show.vue',
        'Development/UserTesting/Index.vue',
        'Development/UserTesting/Legacy.vue',
        'ErrorPage.vue',
        'Privacy.vue',
        'Settings/Profile.vue',
        'Settings/Security.vue',
        'Welcome.vue',
    ]);
});

test('all baseline application routes remain registered', function (): void {
    Artisan::call('route:list', [
        '--except-vendor' => true,
        '--json' => true,
    ]);

    $routes = collect(json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR))
        ->reject(fn (array $route): bool => str_starts_with($route['action'], 'SoutheastCode\\UserManagement\\') || str_starts_with($route['action'], 'SoutheastCode\\Crm\\'));

    expect($routes)->toHaveCount(48);
    $crmRoutes = collect(Illuminate\Support\Facades\Route::getRoutes()->getRoutes())
        ->filter(fn (Illuminate\Routing\Route $route): bool => str_starts_with($route->getActionName(), 'SoutheastCode\\Crm\\'));
    expect($crmRoutes->map(fn (Illuminate\Routing\Route $route): ?string => $route->getName())->values()->all())
        ->toEqualCanonicalizing(['crm.leads.index', 'crm.leads.create', 'crm.leads.store', 'crm.leads.show', 'crm.leads.edit', 'crm.leads.update', 'crm.leads.archive', 'crm.leads.restore', 'crm.leads.convert', 'crm.leads.history', 'crm.leads.notes.store', 'crm.leads.tasks.store', 'crm.leads.tasks.complete', 'crm.options.contacts', 'crm.options.organizations', 'crm.organizations.index', 'crm.organizations.create', 'crm.organizations.store', 'crm.organizations.show', 'crm.organizations.edit', 'crm.organizations.update', 'crm.organizations.archive', 'crm.organizations.restore', 'crm.organizations.destroy', 'crm.organizations.history', 'crm.contacts.index', 'crm.contacts.create', 'crm.contacts.store', 'crm.contacts.show', 'crm.contacts.edit', 'crm.contacts.update', 'crm.contacts.archive', 'crm.contacts.restore', 'crm.contacts.destroy', 'crm.contacts.history']);
    $userRoutes = collect(Illuminate\Support\Facades\Route::getRoutes()->getRoutes())
        ->filter(fn (Illuminate\Routing\Route $route): bool => str_starts_with($route->getActionName(), 'SoutheastCode\\UserManagement\\'));
    expect($userRoutes)->toHaveCount(8)
        ->and($userRoutes->map(fn (Illuminate\Routing\Route $route): ?string => $route->getName())->unique())->toHaveCount(8);
    $auditRoutes = collect(Illuminate\Support\Facades\Route::getRoutes()->getRoutes())
        ->filter(fn (Illuminate\Routing\Route $route): bool => $route->getName() === 'admin.audit.index');
    expect($auditRoutes)->toHaveCount(1)
        ->and($auditRoutes->first()->uri())->toBe('admin/audit');
    $auditPath = Composer\InstalledVersions::getInstallPath('southeastcode/audit');
    expect(collect(File::allFiles($auditPath.'/resources/js/pages'))->map(fn (SplFileInfo $file): string => $file->getRelativePathname())->sort()->values()->all())
        ->toBe(['History/Show.vue', 'Index.vue']);
});

test('temporary frontend compatibility aliases cannot return', function (): void {
    $legacyAliasUsages = collect(File::allFiles(resource_path()))
        ->filter(fn (SplFileInfo $file): bool => in_array($file->getExtension(), ['css', 'ts', 'vue'], true))
        ->filter(fn (SplFileInfo $file): bool => preg_match(
            '/(?<!brand-)\b(?:neutral|muted|accent|teal|violet)-\d+/',
            File::get($file->getPathname()),
        ) === 1)
        ->map(fn (SplFileInfo $file): string => Str::after($file->getPathname(), resource_path().DIRECTORY_SEPARATOR))
        ->sort()
        ->values()
        ->all();

    expect(File::exists(resource_path('css/theme/aliases.css')))->toBeFalse()
        ->and(File::get(resource_path('css/app.css')))->not->toContain('theme/aliases.css')
        ->and($legacyAliasUsages)->toBe([]);
});

test('the frontend source follows the documented naming convention', function (): void {
    $vueArchitectureRoots = ['components', 'layouts', 'pages'];
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

    $frontendFiles = collect(frontendContractFiles());

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

    $invalidAssetPaths = collect(File::allFiles(resource_path('img')))
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
        ->and($invalidAssetPaths)->toBe([])
        ->and(collect($generatedRoots)->every(
            fn (string $root): bool => File::isDirectory(resource_path('js/'.Str::beforeLast($root, '/'))),
        ))->toBeTrue();
});

test('sfc script budgets reject undocumented responsibility exceptions', function (): void {
    $architecture = File::get(base_path('docs/development/frontend-ui-architecture.md'));
    $exceptions = [];

    $scriptLineCount = function (string $contents): int {
        if (preg_match('/<script(?=[^>]*\bsetup\b)[^>]*>([\s\S]*?)<\/script>/', $contents, $matches) !== 1) {
            return 0;
        }

        $count = 0;
        $insideImport = false;

        foreach (preg_split('/\R/', $matches[1]) as $rawLine) {
            $line = mb_trim($rawLine);

            if ($line === '') {
                continue;
            }

            if ($insideImport) {
                if (Str::endsWith($line, ';')) {
                    $insideImport = false;
                }

                continue;
            }

            if (Str::startsWith($line, 'import ')) {
                $insideImport = ! Str::endsWith($line, ';');

                continue;
            }

            $count++;
        }

        return $count;
    };

    $violations = collect(frontendContractFiles())
        ->filter(fn (SplFileInfo $file): bool => $file->getExtension() === 'vue')
        ->filter(fn (SplFileInfo $file): bool => Str::startsWith($file->getRelativePathname(), [
            'components/',
            'layouts/',
            'pages/',
        ]))
        ->mapWithKeys(function (SplFileInfo $file) use ($scriptLineCount): array {
            $path = $file->getRelativePathname();
            $budget = match (true) {
                Str::startsWith($path, 'components/Ui/') => 40,
                Str::startsWith($path, 'pages/') && ! Str::contains($path, '/Components/') => 40,
                default => 60,
            };
            $lines = $scriptLineCount(File::get($file->getPathname()));

            return $lines > $budget
                ? [$path => ['budget' => $budget, 'lines' => $lines]]
                : [];
        })
        ->sortKeys()
        ->all();

    expect(array_keys($violations))->toBe(array_keys($exceptions));

    foreach ($exceptions as $path => $exception) {
        expect($path)->not->toContain('*')
            ->and($exception['reason'])->not->toBeEmpty()
            ->and($violations[$path]['budget'])->toBe($exception['budget'])
            ->and($architecture)->toContain("`resources/js/{$path}`")
            ->and($architecture)->toContain($exception['reason']);
    }
});

test('the current application components and Nuxt UI foundation remain explicit', function (): void {
    foreach ([
        'resources/js/components/Ui/FormField/FormField.vue',
        'resources/js/components/Ui/FormInput/FormInput.vue',
        'resources/js/components/Ui/FormSelect/FormSelect.vue',
        'resources/js/components/Ui/FormMultiSelect/FormMultiSelect.vue',
        'resources/js/components/Ui/FormTextarea/FormTextarea.vue',
        'resources/js/components/WorkspacePage/WorkspacePage.vue',
        'resources/js/lib/utils.ts',
    ] as $foundationPath) {
        expect(File::exists(base_path($foundationPath)))->toBeTrue("Missing frontend foundation: {$foundationPath}");
    }

    foreach (['AsyncCrmSelect/AsyncCrmSelect.vue', 'CrmDetailLayout/CrmDetailLayout.vue'] as $component) {
        expect(File::exists(Composer\InstalledVersions::getInstallPath('southeastcode/crm').'/resources/js/components/'.$component))->toBeTrue();
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
    $asyncCrmSelect = File::get(Composer\InstalledVersions::getInstallPath('southeastcode/crm').'/resources/js/components/AsyncCrmSelect/useCrmOptions.ts');

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
        expect(File::exists(resource_path('js/components/'.Str::beforeLast($component, '.vue')."/{$component}")))
            ->toBeTrue("Missing index product component: {$component}");
    }

    foreach ([
        'Leads' => 'leadIndexColumns.ts',
        'Contacts' => 'contactIndexColumns.ts',
        'Organizations' => 'organizationIndexColumns.ts',
    ] as $domain => $columnFile) {
        $root = Composer\InstalledVersions::getInstallPath('southeastcode/crm')."/resources/js/pages/{$domain}";
        $page = File::get($root.'/Index.vue');
        $columns = File::get($root."/Components/{$columnFile}");

        expect($page)
            ->toContain('useInertiaFilters({')
            ->toContain('<IndexTableFrame')
            ->toContain('<IndexEmptyState')
            ->toContain('<IndexPagination')
            ->toContain(':loading="isLoading"')
            ->not->toContain('setTimeout')
            ->and($columns)
            ->toContain('TableColumn<')
            ->not->toContain('resolveComponent');

        if ($domain !== 'Leads') {
            expect($columns)
                ->toContain('@nuxt/ui/components/Button.vue')
                ->toContain('id: "actions"');
        }
    }

    foreach ([
        'pages/Admin/Users/Components/AdminUsersTableSection.vue',
        'pages/Development/Requests/Index.vue',
        'pages/Development/Review/Index.vue',
    ] as $indexSurface) {
        expect(File::get(str_starts_with($indexSurface, 'pages/Admin/Users/') ? Composer\InstalledVersions::getInstallPath('southeastcode/user-management')."/resources/js/{$indexSurface}" : resource_path("js/{$indexSurface}")))
            ->toContain('<IndexPagination');
    }
});

test('authenticated pages compose the product workspace shell', function (): void {
    $workspacePage = File::get(resource_path('js/components/WorkspacePage/WorkspacePage.vue'));
    $crmDetailLayout = File::get(Composer\InstalledVersions::getInstallPath('southeastcode/crm').'/resources/js/components/CrmDetailLayout/CrmDetailLayout.vue');
    $settingsLayout = File::get(resource_path('js/layouts/SettingsLayout.vue'));
    $workspaceShellOptOuts = [];

    $rawDashboardPanels = collect(frontendContractFiles())
        ->reject(fn (SplFileInfo $file): bool => $file->getRelativePathname() === 'components/WorkspacePage/WorkspacePage.vue')
        ->filter(fn (SplFileInfo $file): bool => Str::contains(File::get($file->getPathname()), '<UDashboardPanel'))
        ->map(fn (SplFileInfo $file): string => $file->getRelativePathname())
        ->values()
        ->all();

    expect($workspacePage)
        ->toContain('<Head :title="documentTitle ?? title" />')
        ->toContain('<UDashboardPanel')
        ->toContain('<WorkspaceNavbar')
        ->toContain('<WorkspaceFooter />')
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
        expect(File::exists(resource_path("js/components/WorkspaceFormPage/{$component}")))
            ->toBeTrue("Missing form composition component: {$component}");
    }

    foreach ([
        'pages/Development/Requests/Create.vue',
    ] as $formPage) {
        expect(File::get(resource_path("js/{$formPage}")))
            ->toContain('<WorkspaceFormPage')
            ->not->toContain('<Form ');
    }

    foreach (['Contacts', 'Organizations', 'Leads'] as $domain) {
        $root = Composer\InstalledVersions::getInstallPath('southeastcode/crm');
        expect(File::get($root."/resources/js/pages/{$domain}/Form.vue"))
            ->toContain('<WorkspaceFormPage')
            ->not->toContain('<Form ');
    }

    $workspaceFormPage = File::get(resource_path('js/components/WorkspaceFormPage/WorkspaceFormPage.vue'));
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
    $applicationLayout = File::get(resource_path('js/layouts/ApplicationLayout.vue'));
    $flashToasts = File::get(resource_path('js/composables/useFlashToasts.ts'));
    $confirmation = File::get(resource_path('js/components/ConfirmActionButton/ConfirmActionButton.vue'));

    expect($applicationLayout)
        ->toContain('useFlashToasts();')
        ->and($flashToasts)
        ->toContain('lastToast')
        ->toContain('watch(() => page.props.flash.success')
        ->and($confirmation)
        ->toContain('<UModal')
        ->toContain('<UButton color="neutral" label="Cancel"');

    foreach (['Contacts', 'Organizations', 'Leads'] as $domain) {
        $root = Composer\InstalledVersions::getInstallPath('southeastcode/crm')."/resources/js/pages/{$domain}";
        expect(File::get($root.'/Show.vue'))
            ->toContain('<CrmDetailLayout')
            ->not->toContain('window.confirm');
    }

    expect(File::get(Composer\InstalledVersions::getInstallPath('southeastcode/crm').'/resources/js/pages/Leads/Components/LeadOverviewSection.vue'))
        ->toContain('<CrmDetailMetadata')
        ->toContain('<ConfirmActionButton')
        ->not->toContain('window.confirm');
});

test('complex workflows keep state inside focused page scoped boundaries', function (): void {
    $twoFactorState = File::get(resource_path('js/composables/useTwoFactorAuth.ts'));
    $landingHeroAssets = File::get(resource_path('js/composables/useLandingHeroAssets.ts'));
    $securityPage = File::get(resource_path('js/pages/Settings/Security.vue'));
    $landingPage = File::get(resource_path('js/pages/Welcome.vue'));
    $landingHeader = File::get(resource_path('js/components/LandingHeader/LandingHeader.vue'));
    $landingContact = File::get(resource_path('js/pages/Welcome/Components/LandingContactForm.vue'));

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
        expect(File::exists(resource_path("js/pages/Settings/Components/{$twoFactorStep}")))
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
        expect(File::exists(resource_path("js/pages/Development/Tasks/Components/{$taskSection}")))
            ->toBeTrue("Missing Development task section: {$taskSection}");
    }
});

test('landing appearance stays stable across server rendering and first paint', function (): void {
    $blade = File::get(resource_path('views/app.blade.php'));
    $bootstrap = File::get(public_path('theme-bootstrap.js'));
    $picture = File::get(resource_path('js/pages/Welcome/Components/LandingHeroPicture.vue'));
    $appearance = File::get(resource_path('js/composables/useAppearance.ts'));

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
        ->and($appearance)
        ->toContain('document.documentElement.dataset.appearance = nextAppearance;')
        ->toContain('document.cookie = `appearance=${nextAppearance}')
        ->toContain('nextAppearance === "system" ? "auto" : nextAppearance');
});

test('route families use shared client and server layout boundaries', function (): void {
    $appEntry = File::get(resource_path('js/app.ts'));
    $serverEntry = File::get(resource_path('js/ssr.ts'));
    $layoutResolver = File::get(resource_path('js/layouts/resolveAppLayout.ts'));
    foreach (['AsyncCrmSelect/AsyncCrmSelect.vue', 'CrmDetailLayout/CrmDetailLayout.vue'] as $component) {
        expect(File::exists(Composer\InstalledVersions::getInstallPath('southeastcode/crm').'/resources/js/components/'.$component))->toBeTrue();
    }

    $viteConfig = File::get(base_path('vite.config.ts'));
    $applicationLayout = File::get(resource_path('js/layouts/ApplicationLayout.vue'));
    $authLayout = File::get(resource_path('js/layouts/AuthLayout.vue'));
    $publicLayout = File::get(resource_path('js/layouts/PublicLayout.vue'));
    $settingsLayout = File::get(resource_path('js/layouts/SettingsLayout.vue'));
    $workspaceSidebar = File::get(resource_path('js/components/WorkspaceSidebar/WorkspaceSidebar.vue'));
    $workspaceSearch = File::get(resource_path('js/components/WorkspaceSearch/WorkspaceSearch.vue'));

    $pagesDeclaringApplicationLayout = collect(File::allFiles(resource_path('js/pages')))
        ->filter(fn (SplFileInfo $page): bool => Str::contains(
            File::get($page->getPathname()),
            '@/layouts/ApplicationLayout.vue',
        ))
        ->map(fn (SplFileInfo $page): string => $page->getRelativePathname())
        ->values()
        ->all();

    expect($appEntry)
        ->toContain('import { resolveAppLayout } from "@/layouts/resolveAppLayout";')
        ->toContain('layout: resolveAppLayout,')
        ->toContain('createSSRApp({ render: () => h(UApp')
        ->not->toContain('VITE_APP_ENV')
        ->and($serverEntry)
        ->toContain('import { resolveAppLayout } from "@/layouts/resolveAppLayout";')
        ->toContain('layout: resolveAppLayout,')
        ->toContain('createSSRApp({ render: () => h(UApp')
        ->not->toContain('VITE_APP_ENV')
        ->and($layoutResolver)
        ->toContain('export function resolveAppLayout(name: string, _page: Page): Component | Component[] | null')
        ->toContain('name.startsWith("Auth/")')
        ->toContain('return [ApplicationLayout, SettingsLayout];')
        ->and($viteConfig)
        ->toContain('defineConfig(({ isSsrBuild }) => ({')
        ->toContain('entry: "resources/js/ssr.ts"')
        ->toContain('sourcemap: !isSsrBuild')
        ->and($pagesDeclaringApplicationLayout)->toBe([])
        ->and($applicationLayout)
        ->toContain('<WorkspaceSidebar')
        ->toContain('useWorkspaceNavigation')
        ->not->toContain('@/routes/')
        ->and($authLayout)
        ->toContain('<AppearanceMenu />')
        ->toContain('<Card as="section"')
        ->and($publicLayout)
        ->toContain('<LandingHeader />')
        ->toContain('<LandingFooter :columns="footerColumns" />')
        ->and($settingsLayout)
        ->toContain('profileEdit.url()')
        ->toContain('securityShow.url()')
        ->and($workspaceSidebar)
        ->toContain('aria-label="Primary workspace navigation"')
        ->toContain('<UserMenu :collapsed="collapsed" />')
        ->and($workspaceSearch)
        ->toContain('aria-label", "Workspace navigation search results"');
});
