<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

test('the advanced behavior registry covers every Task 8 capability', function (): void {
    $registry = File::get(resource_path('js/lib/advancedBehaviorCapabilities.ts'));

    foreach ([
        'accordion',
        'calendar',
        'chart',
        'date-time',
        'dropdown',
        'editor',
        'kanban',
        'map',
        'modal',
        'pagination',
        'popover',
        'select',
        'table',
        'tabs',
        'toast',
        'tooltip',
        'upload',
    ] as $capability) {
        expect($registry)->toContain("id: \"{$capability}\"");
    }

    expect($registry)
        ->toContain('status: "adapted"')
        ->toContain('status: "foundation"')
        ->toContain('lazy: true')
        ->toContain('manifestBehaviors:');
});

test('new dependencies have recorded decision criteria and current package contracts', function (): void {
    $registry = File::get(resource_path('js/lib/advancedBehaviorCapabilities.ts'));
    $package = json_decode(File::get(base_path('package.json')), true, flags: JSON_THROW_ON_ERROR);

    expect($registry)
        ->toContain('necessity:')
        ->toContain('license:')
        ->toContain('maintenance:')
        ->toContain('accessibility:')
        ->toContain('bundleImpact:')
        ->toContain('vueCompatibility:');

    foreach ([
        'vite-plus' => 'MIT',
        '@unovis/ts' => 'Apache-2.0',
        '@internationalized/date' => 'Apache-2.0',
        'maplibre-gl' => 'BSD-3-Clause',
        'vue-eslint-parser' => 'MIT',
    ] as $dependency => $license) {
        expect($registry)
            ->toContain("packageName: \"{$dependency}\"")
            ->toContain("license: \"{$license}\"");
    }

    expect($package['devDependencies']['vite-plus'])->toBe('0.3.0')
        ->and($package['dependencies']['@unovis/ts'])->toBe('^1.6.7')
        ->and($package['dependencies']['@internationalized/date'])->toBe('^3.12.3')
        ->and($package['dependencies']['maplibre-gl'])->toBe('^6.6.0')
        ->and($package['devDependencies']['vue-eslint-parser'])->toBe('^10.2.0');
});

test('Vite Plus owns development and build commands without removing project quality gates', function (): void {
    $package = json_decode(File::get(base_path('package.json')), true, flags: JSON_THROW_ON_ERROR);
    $composer = File::get(base_path('composer.json'));
    $vite = File::get(base_path('vite.config.ts'));

    expect($package['scripts'])
        ->toMatchArray([
            'build' => 'vp build && vp build --ssr',
            'dev' => 'vp dev',
            'lint:check' => 'eslint . && yarn test:frontend-architecture',
            'typecheck' => 'vue-tsc --noEmit',
        ])
        ->and($package['scripts']['check'])
        ->toContain('vp check')
        ->toContain('yarn lint:check')
        ->toContain('yarn format:check')
        ->toContain('yarn typecheck')
        ->and($vite)
        ->toContain('from "vite-plus"')
        ->toContain('lazyPlugins(() => [')
        ->toContain('fmt: false')
        ->and($composer)
        ->toContain('yarn install --immutable')
        ->toContain('yarn up \"*\"')
        ->not->toContain('--frozen-lockfile')
        ->not->toContain('yarn upgrade')
        ->not->toContain('@php artisan @php artisan');
});

test('expensive vendors stay behind typed lazy adapters and clean up imperative state', function (): void {
    $chart = File::get(resource_path('js/components/AdvancedChart/AdvancedChart.vue'));
    $chartAdapter = File::get(resource_path('js/components/AdvancedChart/useAdvancedChart.ts'));
    $chartIndex = File::get(resource_path('js/components/AdvancedChart/index.ts'));
    $editor = File::get(resource_path('js/components/AdvancedEditor/AdvancedEditor.vue'));
    $editorIndex = File::get(resource_path('js/components/AdvancedEditor/index.ts'));
    $map = File::get(resource_path('js/components/AdvancedMap/AdvancedMap.vue'));
    $mapAdapter = File::get(resource_path('js/components/AdvancedMap/useAdvancedMap.ts'));
    $mapIndex = File::get(resource_path('js/components/AdvancedMap/index.ts'));
    $applicationEntry = File::get(resource_path('js/app.ts'));
    $ssrEntry = File::get(resource_path('js/ssr.ts'));

    expect($chartAdapter)
        ->toContain('import("@unovis/vue/components/line")')
        ->toContain('import("@unovis/vue/containers/xy-container")')
        ->toContain('usePreferredReducedMotion()')
        ->toContain('reducedMotion.value === "reduce" ? 0 : 300')
        ->and($chartIndex)
        ->toContain('LazyAdvancedChart')
        ->toContain('import("./lazy")')
        ->not->toContain('import("./AdvancedChart.vue")')
        ->and($chart)
        ->toContain('No chart data is available.')
        ->toContain('View chart data')
        ->toContain('Chart unavailable')
        ->and($editorIndex)
        ->toContain('LazyAdvancedEditor')
        ->toContain('import("./lazy")')
        ->not->toContain('import("./AdvancedEditor.vue")')
        ->and($editor)
        ->toContain('content-type="html"')
        ->toContain('Editor content could not be parsed')
        ->and($mapAdapter)
        ->toContain('await import("maplibre-gl")')
        ->toContain('await import("maplibre-gl/dist/maplibre-gl-worker.mjs?worker&url")')
        ->toContain('maplibre.setWorkerUrl(workerUrl)')
        ->toContain('await import("maplibre-gl/dist/maplibre-gl.css")')
        ->toContain('onBeforeUnmount')
        ->toContain('map?.remove()')
        ->and($mapIndex)
        ->toContain('LazyAdvancedMap')
        ->toContain('import("./lazy")')
        ->not->toContain('import("./AdvancedMap.vue")')
        ->and($map)
        ->toContain('Map locations')
        ->toContain('Map unavailable')
        ->and($applicationEntry)
        ->not->toContain('AdvancedChart', 'AdvancedEditor', 'AdvancedMap', '@unovis', 'maplibre-gl')
        ->and($ssrEntry)
        ->not->toContain('AdvancedChart', 'AdvancedEditor', 'AdvancedMap', '@unovis', 'maplibre-gl');
});

test('first-party adapters expose SSR-safe values and accessible interaction states', function (): void {
    $calendar = File::get(resource_path('js/components/AdvancedCalendar/AdvancedCalendar.vue'));
    $dateTime = File::get(resource_path('js/components/AdvancedDateTime/AdvancedDateTime.vue'));
    $kanban = File::get(resource_path('js/components/AdvancedKanban/AdvancedKanban.vue'));
    $kanbanAdapter = File::get(resource_path('js/components/AdvancedKanban/useAccessibleKanban.ts'));

    expect($calendar)
        ->toContain('modelValue?: string | null')
        ->toContain('value?.toString() ?? null')
        ->toContain('Invalid calendar value')
        ->and($dateTime)
        ->toContain('type="date"')
        ->toContain('type="time"')
        ->and($kanban)
        ->toContain('use Left or Right Arrow')
        ->toContain('aria-live="polite"')
        ->toContain('@dragstart=')
        ->toContain('@keydown=')
        ->toContain("reducedMotion === 'reduce'")
        ->toContain('No cards in this column.')
        ->and($kanbanAdapter)
        ->toContain('["ArrowLeft", "ArrowRight"]')
        ->toContain('requestAnimationFrame')
        ->toContain('CSS.escape(card.id)')
        ->toContain('onBeforeUnmount');
});

test('the implementation decisions are recorded in the Argon blueprint', function (): void {
    $blueprint = File::get(base_path('docs/development/argon-frontend-overhaul.md'));

    expect($blueprint)
        ->toContain('Task 8 established the following concrete boundaries:')
        ->toContain('LazyAdvancedChart')
        ->toContain('LazyAdvancedEditor')
        ->toContain('LazyAdvancedMap')
        ->toContain('| `vite-plus` 0.3.0 |')
        ->toContain('| `maplibre-gl` 6.6.0 |');
});
