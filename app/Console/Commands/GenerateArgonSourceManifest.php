<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use JsonException;
use RuntimeException;
use Symfony\Component\Finder\SplFileInfo;

#[Signature('argon:generate-source-manifest {source : Absolute path to the licensed Argon Dashboard 3 Pro source} {--check : Fail when the committed manifest differs from the source}')]
#[Description('Generate the metadata-only Argon source manifest without copying licensed source files.')]
class GenerateArgonSourceManifest extends Command
{
    private const string MANIFEST_PATH = 'resources/argon/source-manifest.json';

    /** @var array<string, int> */
    private const array EXPECTED_FAMILY_COUNTS = [
        'account' => 4,
        'applications' => 5,
        'authentication' => 17,
        'dashboards' => 5,
        'ecommerce' => 8,
        'general' => 22,
        'projects' => 3,
        'team' => 5,
    ];

    /** @var array<string, string> */
    private const array PLUGIN_BEHAVIORS = [
        'amcharts' => 'vector-maps',
        'argon-dashboard-runtime' => 'responsive-shell-and-theme',
        'bootstrap' => 'bootstrap-interactions',
        'chartjs' => 'charts',
        'choices' => 'enhanced-selects',
        'countup' => 'animated-counters',
        'datatables' => 'data-tables',
        'dragula' => 'drag-and-drop',
        'dropzone' => 'file-upload',
        'flatpickr' => 'date-and-time-pickers',
        'fullcalendar' => 'calendar',
        'google-maps' => 'maps',
        'jkanban' => 'kanban',
        'leaflet' => 'maps',
        'moment' => 'date-and-time-formatting',
        'multistep-form' => 'multi-step-forms',
        'nouislider' => 'range-sliders',
        'orbit-controls' => 'three-dimensional-controls',
        'perfect-scrollbar' => 'custom-scrollbars',
        'photoswipe' => 'image-gallery',
        'popper' => 'positioned-overlays',
        'quill' => 'rich-text-editing',
        'round-slider' => 'round-sliders',
        'smooth-scrollbar' => 'custom-scrollbars',
        'sweetalert' => 'alerts-and-confirmation',
        'threejs' => 'three-dimensional-scenes',
        'tilt' => 'pointer-tilt',
    ];

    /** @var array<string, array<int, string>> */
    private const array PLUGIN_MARKERS = [
        'amcharts' => ['am4core', 'am4maps'],
        'chartjs' => ['new Chart(', 'Chart.getChart('],
        'choices' => ['new Choices('],
        'countup' => ['new CountUp('],
        'datatables' => ['new simpleDatatables.DataTable('],
        'dragula' => ['dragula('],
        'dropzone' => ['new Dropzone('],
        'flatpickr' => ['flatpickr('],
        'fullcalendar' => ['new FullCalendar.Calendar('],
        'google-maps' => ['google.maps.'],
        'jkanban' => ['new jKanban('],
        'leaflet' => ['L.map('],
        'multistep-form' => ['multistep-form'],
        'nouislider' => ['noUiSlider.create('],
        'orbit-controls' => ['new THREE.OrbitControls('],
        'photoswipe' => ['new PhotoSwipe('],
        'quill' => ['new Quill('],
        'round-slider' => ['roundSlider('],
        'sweetalert' => ['Swal.fire('],
        'threejs' => ['new THREE.'],
        'tilt' => ['VanillaTilt.init('],
    ];

    public function handle(): int
    {
        $sourceArgument = $this->argument('source');
        $sourceRoot = realpath($sourceArgument);

        if ($sourceRoot === false || ! is_dir("{$sourceRoot}/pages") || ! is_file("{$sourceRoot}/package.json")) {
            $this->error('The source path is not an Argon Dashboard 3 Pro checkout.');

            return self::FAILURE;
        }

        try {
            $manifest = $this->buildManifest($sourceRoot);
            $encodedManifest = $this->encodeManifest($manifest);
        } catch (JsonException|RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $manifestPath = base_path(self::MANIFEST_PATH);

        if ((bool) $this->option('check')) {
            if (! is_file($manifestPath) || File::get($manifestPath) !== $encodedManifest) {
                $this->error('The committed Argon source manifest is stale. Regenerate it from the licensed source.');

                return self::FAILURE;
            }

            $this->info('The committed Argon source manifest matches the licensed source snapshot.');

            return self::SUCCESS;
        }

        File::ensureDirectoryExists(dirname($manifestPath));
        File::put($manifestPath, $encodedManifest);

        $this->info("Generated {$manifestPath}.");

        return self::SUCCESS;
    }

    /** @return array<string, mixed> */
    private function buildManifest(string $sourceRoot): array
    {
        $package = $this->decodeJsonFile("{$sourceRoot}/package.json");
        $sourcePaths = $this->sourcePaths($sourceRoot);
        $sourcePathLookup = array_fill_keys($sourcePaths, true);
        $examples = $this->examples($sourceRoot, $sourcePaths, $sourcePathLookup);
        $referencedBy = $this->referencedBy($examples);
        $files = $this->files($sourceRoot, $sourcePaths, $referencedBy);
        [$files, $duplicateGroups] = $this->recordDuplicateGroups($files);
        $dependencies = $this->dependencies($files, $examples);
        $familyCounts = $this->countsBy($examples, 'family');

        if ($familyCounts !== self::EXPECTED_FAMILY_COUNTS) {
            throw new RuntimeException('The licensed example family counts no longer match the approved 69-page baseline.');
        }

        $snapshotInput = array_map(
            static fn (array $file): string => "{$file['path']}\0{$file['sha256']}",
            $files,
        );

        return [
            'schema_version' => 1,
            'source' => [
                'product' => 'Argon Dashboard 3 Pro',
                'package_name' => $package['name'] ?? null,
                'version' => $package['version'] ?? null,
                'package_license' => $package['license'] ?? null,
                'source_root' => 'argon-dashboard-3-pro',
                'snapshot_sha256' => hash('sha256', implode("\n", $snapshotInput)),
                'file_count' => count($files),
                'example_count' => count($examples),
                'empty_example_count' => count(array_filter(
                    $examples,
                    static fn (array $example): bool => $example['source_status'] === 'empty',
                )),
            ],
            'authorization' => [
                'application_use' => 'Authorized under the team Creative Tim Club membership and Access Pass.',
                'source_publication' => 'The licensed source files are not stored in this repository.',
                'future_extraction' => 'Requires a separate license and asset distribution review.',
                'official_license_url' => 'https://www.creative-tim.com/license',
                'licensing_questions' => 'docs/development/argon-source-licensing.md',
            ],
            'classification_definitions' => $this->classificationDefinitions(),
            'family_counts' => $familyCounts,
            'example_source_status_counts' => $this->countsBy($examples, 'source_status'),
            'file_kind_counts' => $this->countsBy($files, 'kind'),
            'adaptation_state_counts' => $this->countsBy($files, 'adaptation_state'),
            'provenance_counts' => $this->countsBy($files, 'provenance'),
            'distribution_counts' => $this->countsBy($files, 'distribution'),
            'examples' => $examples,
            'dependencies' => $dependencies,
            'duplicate_groups' => $duplicateGroups,
            'files' => $files,
        ];
    }

    /** @param array<string, mixed> $manifest */
    private function encodeManifest(array $manifest): string
    {
        $json = json_encode(
            $manifest,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
        );

        return preg_replace_callback(
            '/^( +)/m',
            static fn (array $matches): string => str_repeat(' ', intdiv(mb_strlen($matches[1]), 2)),
            $json,
        ).PHP_EOL;
    }

    /** @return array<string, mixed> */
    private function decodeJsonFile(string $path): array
    {
        $decoded = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($decoded)) {
            throw new RuntimeException("{$path} does not contain a JSON object.");
        }

        return $decoded;
    }

    /** @return array<int, string> */
    private function sourcePaths(string $sourceRoot): array
    {
        $paths = array_map(
            fn (SplFileInfo $file): string => $this->relativePath($sourceRoot, $file->getPathname()),
            File::allFiles($sourceRoot),
        );

        sort($paths, SORT_STRING);

        return $paths;
    }

    /**
     * @param  array<int, string>  $sourcePaths
     * @param  array<string, bool>  $sourcePathLookup
     * @return array<int, array<string, mixed>>
     */
    private function examples(string $sourceRoot, array $sourcePaths, array $sourcePathLookup): array
    {
        $examples = [];

        foreach ($sourcePaths as $sourcePath) {
            if (! str_starts_with($sourcePath, 'pages/') || ! str_ends_with($sourcePath, '.html')) {
                continue;
            }

            $html = File::get("{$sourceRoot}/{$sourcePath}");
            [$assets, $externalUrls, $missingReferences] = $this->resourceReferences(
                $sourcePath,
                $html,
                $sourcePathLookup,
            );

            if ($missingReferences !== []) {
                throw new RuntimeException("{$sourcePath} references missing files: ".implode(', ', $missingReferences));
            }

            $family = $this->family($sourcePath);
            $id = $this->exampleId($sourcePath, $family);
            $plugins = $this->examplePlugins($assets, $externalUrls);
            $sourceStatus = $html === '' ? 'empty' : 'available';

            $examples[] = [
                'id' => $id,
                'family' => $family,
                'source_path' => $sourcePath,
                'source_status' => $sourceStatus,
                'title' => $this->exampleTitle($sourcePath, $family),
                'document_title' => $this->documentTitle($html),
                'layout' => $this->layout($sourcePath, $html),
                'assets' => $assets,
                'external_assets' => $externalUrls,
                'plugins' => $plugins,
                'dependencies' => $this->exampleDependencies($externalUrls, $plugins),
                'behaviors' => $this->behaviors($html, $plugins),
                'planned_vue_destination' => $this->plannedVueDestination($sourcePath, $family),
                'adaptation_state' => 'adapted',
            ];
        }

        usort($examples, static fn (array $left, array $right): int => $left['source_path'] <=> $right['source_path']);

        return $examples;
    }

    /**
     * @param  array<string, bool>  $sourcePathLookup
     * @return array{array<int, string>, array<int, string>, array<int, string>}
     */
    private function resourceReferences(string $sourcePath, string $html, array $sourcePathLookup): array
    {
        $references = [];
        $resourcePattern = '~<(?:script|img|link|source|video|input|image)\b[^>]*\b(?:src|href|poster|data-src|data-background)\s*=\s*["\']([^"\']+)["\']~i';
        $stylePattern = '~url\(\s*["\']?([^\)"\']+)["\']?\s*\)~i';

        preg_match_all($resourcePattern, $html, $resourceMatches);
        preg_match_all($stylePattern, $html, $styleMatches);

        foreach (array_merge($resourceMatches[1], $styleMatches[1]) as $reference) {
            $references[] = html_entity_decode(mb_trim($reference));
        }

        $assets = [];
        $externalUrls = [];
        $missingReferences = [];

        foreach (array_unique($references) as $reference) {
            if ($this->isIgnoredReference($reference)) {
                continue;
            }

            if (Str::startsWith($reference, ['http://', 'https://', '//'])) {
                $externalUrls[] = str_starts_with($reference, '//') ? "https:{$reference}" : $reference;

                continue;
            }

            $resolvedPath = $this->resolveReference($sourcePath, $reference);

            if (isset($sourcePathLookup[$resolvedPath])) {
                $assets[] = $resolvedPath;

                continue;
            }

            $missingReferences[] = $resolvedPath;
        }

        sort($assets, SORT_STRING);
        sort($externalUrls, SORT_STRING);
        sort($missingReferences, SORT_STRING);

        return [
            array_values(array_unique($assets)),
            array_values(array_unique($externalUrls)),
            array_values(array_unique($missingReferences)),
        ];
    }

    private function isIgnoredReference(string $reference): bool
    {
        return $reference === ''
            || Str::contains($reference, ['files[', 'createObjectURL'])
            || Str::startsWith($reference, [
                '#',
                'data:',
                'javascript:',
                'mailto:',
                'tel:',
            ]);
    }

    private function resolveReference(string $sourcePath, string $reference): string
    {
        $referencePath = parse_url($reference, PHP_URL_PATH);
        $referencePath = is_string($referencePath) ? rawurldecode($referencePath) : $reference;

        if (str_starts_with($referencePath, '/argon-dashboard-pro/')) {
            return Str::after($referencePath, '/argon-dashboard-pro/');
        }

        if (str_starts_with($referencePath, '/assets/')) {
            return mb_ltrim($referencePath, '/');
        }

        return $this->normalizePath(dirname($sourcePath).'/'.$referencePath);
    }

    private function normalizePath(string $path): string
    {
        $segments = [];

        foreach (explode('/', str_replace('\\', '/', $path)) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }

            if ($segment === '..') {
                array_pop($segments);

                continue;
            }

            $segments[] = $segment;
        }

        return implode('/', $segments);
    }

    private function family(string $sourcePath): string
    {
        $family = explode('/', $sourcePath)[1] ?? '';

        return $family === 'pages' ? 'general' : $family;
    }

    private function exampleId(string $sourcePath, string $family): string
    {
        $segments = $this->exampleSegments($sourcePath, $family);

        return Str::slug($family.'-'.implode('-', $segments));
    }

    private function exampleTitle(string $sourcePath, string $family): string
    {
        $segments = array_map($this->headline(...), $this->exampleSegments($sourcePath, $family));

        return implode(' ', $segments);
    }

    /** @return array<int, string> */
    private function exampleSegments(string $sourcePath, string $family): array
    {
        $withoutExtension = Str::beforeLast($sourcePath, '.html');
        $prefix = $family === 'general' ? 'pages/pages/' : "pages/{$family}/";

        return explode('/', Str::after($withoutExtension, $prefix));
    }

    private function headline(string $value): string
    {
        return match ($value) {
            'signin' => 'Sign In',
            'signup' => 'Sign Up',
            'datatables' => 'DataTables',
            'rtl' => 'RTL',
            'vr' => 'VR',
            default => Str::headline($value),
        };
    }

    private function documentTitle(string $html): ?string
    {
        preg_match('~<title>(.*?)</title>~is', $html, $matches);

        $documentTitle = Str::squish(strip_tags($matches[1] ?? ''));

        return $documentTitle === '' ? null : $documentTitle;
    }

    private function layout(string $sourcePath, string $html): string
    {
        if ($html === '') {
            return 'unavailable-empty-source';
        }

        if (str_starts_with($sourcePath, 'pages/authentication/')) {
            return 'authentication-'.Str::beforeLast(basename($sourcePath), '.html');
        }

        if (str_contains($sourcePath, '/vr/')) {
            return 'virtual-reality';
        }

        if (str_ends_with($sourcePath, '/rtl-page.html')) {
            return 'rtl-sidebar';
        }

        if (str_contains($html, '<aside')) {
            return 'application-sidebar';
        }

        return 'standalone';
    }

    private function plannedVueDestination(string $sourcePath, string $family): string
    {
        $segments = array_map($this->studly(...), $this->exampleSegments($sourcePath, $family));
        $destination = implode('/', $segments);

        return 'resources/js/pages/Admin/ArgonGallery/Examples/'.Str::studly($family)."/{$destination}.vue";
    }

    private function studly(string $value): string
    {
        return match ($value) {
            'signin' => 'SignIn',
            'signup' => 'SignUp',
            'datatables' => 'DataTables',
            'rtl' => 'Rtl',
            'vr' => 'VirtualReality',
            '404' => 'Error404',
            '500' => 'Error500',
            default => Str::studly($value),
        };
    }

    /**
     * @param  array<int, string>  $assets
     * @param  array<int, string>  $externalUrls
     * @return array<int, string>
     */
    private function examplePlugins(array $assets, array $externalUrls): array
    {
        $plugins = [];

        foreach ($assets as $asset) {
            $plugin = $this->pluginIdForPath($asset);

            if ($plugin !== null) {
                $plugins[] = $plugin;
            }
        }

        foreach ($externalUrls as $externalUrl) {
            $dependency = $this->externalDependency($externalUrl);

            if ($dependency['runtime']) {
                $plugins[] = $dependency['id'];
            }
        }

        sort($plugins, SORT_STRING);

        return array_values(array_unique($plugins));
    }

    /**
     * @param  array<int, string>  $externalUrls
     * @param  array<int, string>  $plugins
     * @return array<int, string>
     */
    private function exampleDependencies(array $externalUrls, array $plugins): array
    {
        $dependencies = $plugins;

        foreach ($externalUrls as $externalUrl) {
            $dependencies[] = $this->externalDependency($externalUrl)['id'];
        }

        sort($dependencies, SORT_STRING);

        return array_values(array_unique($dependencies));
    }

    /**
     * @param  array<int, string>  $plugins
     * @return array<int, string>
     */
    private function behaviors(string $html, array $plugins): array
    {
        if ($html === '') {
            return ['source-content-missing'];
        }

        $behaviors = [];

        preg_match_all('~data-bs-toggle=["\']([^"\']+)["\']~i', $html, $toggleMatches);

        foreach ($toggleMatches[1] as $toggle) {
            $behaviors[] = 'bootstrap-'.Str::slug($toggle);
        }

        if (in_array('argon-dashboard-runtime', $plugins, true)) {
            $behaviors[] = self::PLUGIN_BEHAVIORS['argon-dashboard-runtime'];
        }

        foreach (self::PLUGIN_MARKERS as $plugin => $markers) {
            if (in_array($plugin, $plugins, true) && Str::contains($html, $markers)) {
                $behaviors[] = self::PLUGIN_BEHAVIORS[$plugin];
            }
        }

        if (
            in_array('popper', $plugins, true)
            && array_intersect($behaviors, ['bootstrap-dropdown', 'bootstrap-popover', 'bootstrap-tooltip']) !== []
        ) {
            $behaviors[] = self::PLUGIN_BEHAVIORS['popper'];
        }

        if (preg_match('~\bon(?:click|change|input|submit|load)=~i', $html) === 1) {
            $behaviors[] = 'inline-event-handlers';
        }

        sort($behaviors, SORT_STRING);

        return array_values(array_unique($behaviors));
    }

    private function pluginIdForPath(string $path): ?string
    {
        if (preg_match('~^assets/js/argon-dashboard(?:\.min)?\.js$~', $path) === 1) {
            return 'argon-dashboard-runtime';
        }

        if (str_starts_with($path, 'assets/js/core/bootstrap')) {
            return 'bootstrap';
        }

        if ($path === 'assets/js/core/popper.min.js') {
            return 'popper';
        }

        if (! str_starts_with($path, 'assets/js/plugins/')) {
            return null;
        }

        $pluginPath = Str::after($path, 'assets/js/plugins/');
        $pluginName = str_contains($pluginPath, '/')
            ? Str::before($pluginPath, '/')
            : Str::before($pluginPath, '.js');

        return Str::of($pluginName)->replace('.min', '')->kebab()->toString();
    }

    /** @return array{id: string, kind: string, runtime: bool} */
    private function externalDependency(string $url): array
    {
        $host = parse_url($url, PHP_URL_HOST);
        $host = is_string($host) ? mb_strtolower($host) : 'external';

        return match ($host) {
            'fonts.googleapis.com' => [
                'id' => 'google-fonts-open-sans',
                'kind' => 'font-service',
                'runtime' => false,
            ],
            'kit.fontawesome.com' => [
                'id' => 'font-awesome-kit',
                'kind' => 'icon-runtime',
                'runtime' => true,
            ],
            'maps.googleapis.com' => [
                'id' => 'google-maps',
                'kind' => 'map-runtime',
                'runtime' => true,
            ],
            'demos.creative-tim.com' => [
                'id' => 'creative-tim-cdn',
                'kind' => 'vendor-style-reference',
                'runtime' => false,
            ],
            default => [
                'id' => 'external-'.Str::slug($host),
                'kind' => 'external-resource',
                'runtime' => false,
            ],
        };
    }

    /**
     * @param  array<int, array<string, mixed>>  $examples
     * @return array<string, array<int, string>>
     */
    private function referencedBy(array $examples): array
    {
        $referencedBy = [];

        foreach ($examples as $example) {
            foreach ($example['assets'] as $asset) {
                $referencedBy[$asset][] = $example['id'];
            }
        }

        foreach ($referencedBy as &$exampleIds) {
            sort($exampleIds, SORT_STRING);
        }

        unset($exampleIds);

        ksort($referencedBy, SORT_STRING);

        return $referencedBy;
    }

    /**
     * @param  array<int, string>  $sourcePaths
     * @param  array<string, array<int, string>>  $referencedBy
     * @return array<int, array<string, mixed>>
     */
    private function files(string $sourceRoot, array $sourcePaths, array $referencedBy): array
    {
        $files = [];

        foreach ($sourcePaths as $sourcePath) {
            [$provenance, $adaptationState, $distribution] = $this->classifications(
                $sourcePath,
                isset($referencedBy[$sourcePath]),
            );

            $files[] = [
                'path' => $sourcePath,
                'kind' => $this->fileKind($sourcePath),
                'bytes' => File::size("{$sourceRoot}/{$sourcePath}"),
                'sha256' => hash_file('sha256', "{$sourceRoot}/{$sourcePath}"),
                'provenance' => $provenance,
                'adaptation_state' => $adaptationState,
                'distribution' => $distribution,
                'referenced_by' => $referencedBy[$sourcePath] ?? [],
                'duplicate_group' => null,
            ];
        }

        return $files;
    }

    /** @return array{string, string, string} */
    private function classifications(string $path, bool $isReferenced): array
    {
        if (str_starts_with($path, 'pages/')) {
            return ['creative-tim-licensed', 'adapted', 'application-only'];
        }

        if (str_starts_with($path, 'assets/js/core/') || str_starts_with($path, 'assets/js/plugins/')) {
            return ['third-party-bundled', 'runtime-only', 'runtime-evaluation-only'];
        }

        if (str_starts_with($path, 'assets/scss/argon-dashboard/bootstrap/')) {
            $state = str_contains($path, '/tests/') ? 'excluded' : 'adapted';
            $distribution = $state === 'excluded' ? 'excluded' : 'reference-only';

            return ['third-party-bundled', $state, $distribution];
        }

        if ($path === 'assets/js/argon-dashboard.js') {
            return ['creative-tim-licensed', 'adapted', 'application-only'];
        }

        if (
            str_starts_with($path, 'assets/css/argon-dashboard')
            || preg_match('~^assets/js/argon-dashboard(?:\.min\.js|\.js\.map)$~', $path) === 1
        ) {
            return ['generated-bundle', 'generated', 'generated-reference-only'];
        }

        if (str_starts_with($path, 'assets/css/nucleo-') || str_starts_with($path, 'assets/fonts/')) {
            return ['bundled-media-unverified', 'adapted', 'application-only-review-before-extraction'];
        }

        if (str_starts_with($path, 'assets/scss/')) {
            return ['creative-tim-licensed', 'adapted', 'application-only'];
        }

        if (str_starts_with($path, 'assets/img/')) {
            return $isReferenced
                ? ['bundled-media-unverified', 'adapted', 'application-only-review-before-extraction']
                : ['bundled-media-unverified', 'excluded', 'excluded'];
        }

        return ['source-package-metadata', 'excluded', 'excluded'];
    }

    private function fileKind(string $path): string
    {
        if (str_starts_with($path, 'pages/')) {
            return 'html-example';
        }

        if (str_starts_with($path, 'assets/fonts/')) {
            return 'font';
        }

        if (str_starts_with($path, 'assets/img/icons/')) {
            return 'icon';
        }

        if (str_contains($path, '/logos/') || str_contains(basename($path), 'logo')) {
            return 'logo';
        }

        if (str_starts_with($path, 'assets/img/shapes/') || str_contains(basename($path), 'pattern')) {
            return 'texture';
        }

        if (str_starts_with($path, 'assets/img/')) {
            return 'image';
        }

        if (str_starts_with($path, 'assets/js/plugins/')) {
            return 'javascript-plugin';
        }

        if (str_starts_with($path, 'assets/js/core/')) {
            return 'javascript-vendor-runtime';
        }

        if (str_starts_with($path, 'assets/js/')) {
            return str_ends_with($path, '.map') ? 'javascript-source-map' : 'javascript-theme-runtime';
        }

        if (str_starts_with($path, 'assets/scss/')) {
            return str_contains($path, '/tests/') ? 'sass-test-support' : 'sass';
        }

        if (str_starts_with($path, 'assets/css/')) {
            return str_ends_with($path, '.map') ? 'stylesheet-source-map' : 'stylesheet';
        }

        return 'package-metadata';
    }

    /**
     * @param  array<int, array<string, mixed>>  $files
     * @return array{array<int, array<string, mixed>>, array<int, array<string, mixed>>}
     */
    private function recordDuplicateGroups(array $files): array
    {
        $pathsByHash = [];

        foreach ($files as $file) {
            $pathsByHash[$file['sha256']][] = $file['path'];
        }

        $pathsByHash = array_filter($pathsByHash, static fn (array $paths): bool => count($paths) > 1);
        ksort($pathsByHash, SORT_STRING);
        $duplicateGroups = [];
        $groupByHash = [];
        $index = 1;

        foreach ($pathsByHash as $sha256 => $paths) {
            sort($paths, SORT_STRING);
            $id = sprintf('duplicate-%03d', $index);
            $groupByHash[$sha256] = $id;
            $duplicateGroups[] = [
                'id' => $id,
                'sha256' => $sha256,
                'paths' => $paths,
            ];
            $index++;
        }

        foreach ($files as &$file) {
            $file['duplicate_group'] = $groupByHash[$file['sha256']] ?? null;
        }

        unset($file);

        return [$files, $duplicateGroups];
    }

    /**
     * @param  array<int, array<string, mixed>>  $files
     * @param  array<int, array<string, mixed>>  $examples
     * @return array<int, array<string, mixed>>
     */
    private function dependencies(array $files, array $examples): array
    {
        $dependencies = [];

        foreach ($files as $file) {
            $plugin = $this->pluginIdForPath($file['path']);

            if ($plugin === null) {
                continue;
            }

            $dependencies[$plugin] ??= $this->dependencyRecord(
                id: $plugin,
                kind: 'bundled-javascript-runtime',
                provenance: $file['provenance'],
                distribution: $file['distribution'],
            );
            $dependencies[$plugin]['source_files'][] = $file['path'];
        }

        foreach ($examples as $example) {
            foreach ($example['external_assets'] as $externalUrl) {
                $dependencyId = $this->externalDependency($externalUrl)['id'];
                $dependencies[$dependencyId] ??= $this->dependencyRecordFromId($dependencyId);
                $dependencies[$dependencyId]['source_urls'][] = $externalUrl;
            }

            foreach ($example['dependencies'] as $dependencyId) {
                $dependencies[$dependencyId] ??= $this->dependencyRecordFromId($dependencyId);
                $dependencies[$dependencyId]['required_by'][] = $example['id'];
            }
        }

        foreach ($dependencies as &$dependency) {
            sort($dependency['source_files'], SORT_STRING);
            sort($dependency['source_urls'], SORT_STRING);
            sort($dependency['required_by'], SORT_STRING);
            $dependency['source_files'] = array_values(array_unique($dependency['source_files']));
            $dependency['source_urls'] = array_values(array_unique($dependency['source_urls']));
            $dependency['required_by'] = array_values(array_unique($dependency['required_by']));
        }

        unset($dependency);

        ksort($dependencies, SORT_STRING);

        return array_values($dependencies);
    }

    /** @return array<string, mixed> */
    private function dependencyRecordFromId(string $id): array
    {
        return match ($id) {
            'google-fonts-open-sans' => $this->dependencyRecord(
                $id,
                'font-service',
                'external-service',
                'replace-or-self-host-before-extraction',
            ),
            'font-awesome-kit' => $this->dependencyRecord(
                $id,
                'icon-runtime',
                'external-service',
                'runtime-evaluation-only',
            ),
            'google-maps' => $this->dependencyRecord(
                $id,
                'map-runtime',
                'external-service',
                'runtime-evaluation-only',
            ),
            'creative-tim-cdn' => $this->dependencyRecord(
                $id,
                'vendor-style-reference',
                'creative-tim-licensed',
                'application-only',
            ),
            default => $this->dependencyRecord(
                $id,
                'external-resource',
                'external-service',
                'review-required',
            ),
        };
    }

    /** @return array<string, mixed> */
    private function dependencyRecord(
        string $id,
        string $kind,
        string $provenance,
        string $distribution,
    ): array {
        return [
            'id' => $id,
            'kind' => $kind,
            'version' => null,
            'license' => 'review-required',
            'provenance' => $provenance,
            'distribution' => $distribution,
            'decision' => $this->dependencyDecision($id),
            'source_files' => [],
            'source_urls' => [],
            'required_by' => [],
        ];
    }

    private function dependencyDecision(string $id): string
    {
        return match ($id) {
            'argon-dashboard-runtime' => 'Adapt required behavior as first-party Vue code; do not ship this runtime.',
            'bootstrap', 'popper' => 'Replace with native HTML, Reka UI, or existing Vue behavior.',
            'google-fonts-open-sans' => 'Decide whether to self-host an approved font asset before public extraction.',
            'creative-tim-cdn' => 'Use the local licensed source as reference; do not depend on the demo CDN.',
            default => 'Evaluate necessity, license, accessibility, bundle cost, and Vue compatibility in Task 8.',
        };
    }

    /** @return array<string, array<string, string>> */
    private function classificationDefinitions(): array
    {
        return [
            'adaptation_state' => [
                'authored' => 'Created specifically for this application; reserved for future manifest extensions.',
                'adapted' => 'Used as licensed implementation reference or intentionally adapted into first-party code.',
                'generated' => 'Compiled, minified, or source-map output; retained as reference metadata only.',
                'runtime-only' => 'Bundled runtime evaluated for replacement or a typed Vue adapter; not copied automatically.',
                'excluded' => 'Not planned for application or starter-kit distribution.',
            ],
            'provenance' => [
                'creative-tim-licensed' => 'Creative Tim premium source covered by the project authorization.',
                'third-party-bundled' => 'Third-party source bundled inside the licensed package.',
                'generated-bundle' => 'Compiled or minified output distributed in the licensed package.',
                'bundled-media-unverified' => 'Bundled media whose original author and redistribution terms require review.',
                'source-package-metadata' => 'Build or release metadata from the licensed package.',
                'external-service' => 'Remote dependency referenced by an example page.',
            ],
            'distribution' => [
                'application-only' => 'Authorized for adaptation inside this application; not approved for starter-kit extraction.',
                'application-only-review-before-extraction' => 'May be used in this application but requires asset-level review before extraction.',
                'reference-only' => 'May inform the implementation but is not copied into application output.',
                'runtime-evaluation-only' => 'Evaluate and replace or isolate before any runtime use.',
                'generated-reference-only' => 'Generated vendor output is recorded but not distributed from this repository.',
                'replace-or-self-host-before-extraction' => 'Choose an approved replacement or separately licensed self-hosted asset.',
                'review-required' => 'No distribution decision has been made; resolve before use.',
                'excluded' => 'Do not distribute.',
            ],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $records
     * @return array<string, int>
     */
    private function countsBy(array $records, string $key): array
    {
        $counts = [];

        foreach ($records as $record) {
            $value = $record[$key] ?? null;

            if (! is_string($value)) {
                throw new RuntimeException("Cannot count manifest records by {$key}.");
            }

            $counts[$value] = ($counts[$value] ?? 0) + 1;
        }

        ksort($counts, SORT_STRING);

        return $counts;
    }

    private function relativePath(string $root, string $path): string
    {
        return str_replace('\\', '/', Str::after($path, mb_rtrim($root, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR));
    }
}
