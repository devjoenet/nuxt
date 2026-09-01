<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

test('the Argon foundation is authored, scoped, and explicit in both themes', function (): void {
    $palette = File::get(resource_path('css/theme/palette.css'));
    $foundations = File::get(resource_path('css/theme/foundations.css'));
    $nuxtUi = File::get(resource_path('css/theme/nuxt-ui.css'));
    $blade = File::get(resource_path('views/app.blade.php'));

    expect($palette)
        ->toContain('Argon Dashboard Pro 2.1.0 color references translated into authored palettes.')
        ->toContain('--color-argon-600: #5e72e4;')
        ->toContain('--color-argon-accent-600: #825ee4;')
        ->toContain('--color-green-500: #2dce89;')
        ->toContain('--color-cyan-500: #11cdef;')
        ->toContain('--color-orange-400: #fb6340;')
        ->toContain('--color-red-500: #f5365c;')
        ->and($foundations)
        ->toContain('--font-sans: "Open Sans"')
        ->toContain('--breakpoint-sm: 36rem;')
        ->toContain('--breakpoint-2xl: 87.5rem;')
        ->toContain('--spacing-argon-gutter: 1.5rem;')
        ->toContain('--argon-grid-columns: 12;')
        ->toContain('--argon-border-width: 1px;')
        ->toContain('--radius-pill: 50rem;')
        ->toContain('--shadow-xl:')
        ->toContain('--argon-layer-modal: 70;')
        ->toContain('.dark {')
        ->toContain('[data-argon-app] {')
        ->toContain('@media (prefers-reduced-motion: reduce)')
        ->toContain('the existing Bunny Fonts provider')
        ->toContain('Icons remain the already-installed Phosphor set.')
        ->and($nuxtUi)
        ->toContain('--ui-primary: var(--color-argon-800);')
        ->toContain('--ui-bg: var(--argon-surface-canvas);')
        ->toContain('.dark {')
        ->and($blade)
        ->toContain('open-sans:300,400,600,700')
        ->toContain('data-argon-app');
});

test('core visual primitives expose typed public APIs and CVA variants', function (): void {
    $primitives = [
        'Alert',
        'Avatar',
        'Badge',
        'Breadcrumbs',
        'Button',
        'ButtonGroup',
        'Card',
        'Divider',
        'EmptyState',
        'Icon',
        'List',
        'Progress',
        'Skeleton',
        'Spinner',
        'Stat',
        'Typography',
    ];

    foreach ($primitives as $primitive) {
        $directory = resource_path("js/components/Ui/{$primitive}");

        expect(File::exists("{$directory}/{$primitive}.vue"))->toBeTrue("Missing {$primitive} component")
            ->and(File::exists("{$directory}/index.ts"))->toBeTrue("Missing {$primitive} public API")
            ->and(File::exists("{$directory}/types.ts"))->toBeTrue("Missing {$primitive} types")
            ->and(File::exists("{$directory}/variants.ts"))->toBeTrue("Missing {$primitive} variants")
            ->and(File::get("{$directory}/{$primitive}.vue"))->toContain("defineProps<{$primitive}Props>")
            ->and(File::get("{$directory}/index.ts"))->toContain("./{$primitive}.vue")
            ->and(File::get("{$directory}/variants.ts"))->toContain('cva(');
    }

    foreach (['Alert', 'Badge', 'Breadcrumbs', 'Button', 'ButtonGroup', 'Card', 'Divider', 'EmptyState', 'List', 'Typography'] as $slottedPrimitive) {
        expect(File::get(resource_path("js/components/Ui/{$slottedPrimitive}/{$slottedPrimitive}.vue")))
            ->toContain("defineSlots<{$slottedPrimitive}Slots>()");
    }

    $button = File::get(resource_path('js/components/Ui/Button/Button.vue'));
    $alert = File::get(resource_path('js/components/Ui/Alert/Alert.vue'));

    expect($button)
        ->toContain('defineEmits<{ click: [event: MouseEvent] }>()')
        ->toContain(':aria-busy="loading || undefined"')
        ->toContain(':disabled="disabled || loading"')
        ->and($alert)
        ->toContain('defineEmits<{ dismiss: [] }>()')
        ->toContain('aria-label="Dismiss"')
        ->toContain('@click="emit(\'dismiss\')"');

    $directColorValues = collect(File::allFiles(resource_path('js/components/Ui')))
        ->filter(fn (SplFileInfo $file): bool => in_array($file->getExtension(), ['ts', 'vue'], true))
        ->filter(fn (SplFileInfo $file): bool => preg_match('/#[0-9a-f]{3,8}\b/i', File::get($file->getPathname())) === 1)
        ->map(fn (SplFileInfo $file): string => $file->getRelativePathname())
        ->values()
        ->all();

    expect($directColorValues)->toBe([]);

    $rawValueFiles = collect(File::allFiles(resource_path()))
        ->filter(fn (SplFileInfo $file): bool => in_array($file->getExtension(), ['css', 'ts', 'vue'], true))
        ->filter(fn (SplFileInfo $file): bool => preg_match('/(?:#[0-9a-f]{3,8}\b|rgba?\()/i', File::get($file->getPathname())) === 1)
        ->map(fn (SplFileInfo $file): string => Str::after($file->getPathname(), resource_path().DIRECTORY_SEPARATOR))
        ->reject(fn (string $path): bool => in_array($path, [
            'css/theme/foundations.css',
            'css/theme/palette.css',
        ], true))
        ->sort()
        ->values()
        ->all();

    expect($rawValueFiles)->toBe([
        'js/components/Landing/LandingHero.vue',
        'js/components/Landing/MetricCard.vue',
        'js/components/Landing/ProcessTimeline.vue',
        'js/pages/Welcome.vue',
        'js/sections/Landing/LandingContactSection.vue',
        'js/sections/Landing/LandingOperationsSection.vue',
    ]);
});

test('Argon navigation shells share semantic chrome and accessible overlays', function (): void {
    $workspaceNavbar = File::get(resource_path('js/components/WorkspaceNavbar/WorkspaceNavbar.vue'));
    $workspaceSidebar = File::get(resource_path('js/components/WorkspaceSidebar.vue'));
    $landingHeader = File::get(resource_path('js/components/Landing/LandingHeader.vue'));
    $appearanceMenu = File::get(resource_path('js/components/AppearanceMenu/AppearanceMenu.vue'));
    $breadcrumbs = File::get(resource_path('js/components/Ui/Breadcrumbs/Breadcrumbs.vue'));

    expect($workspaceNavbar)
        ->toContain('<Breadcrumbs')
        ->toContain('<AppearanceMenu />')
        ->toContain('bg-surface-translucent')
        ->and($workspaceSidebar)
        ->toContain('v-model:open="open"')
        ->toContain('bg-surface-elevated')
        ->and($landingHeader)
        ->toContain('<USlideover v-model:open="mobileNavigationOpen"')
        ->toContain('data-testid="public-navigation-trigger"')
        ->toContain('aria-label="Mobile navigation"')
        ->and($appearanceMenu)
        ->toContain('data-testid="appearance-menu-trigger"')
        ->toContain('type: "checkbox" as const')
        ->and($breadcrumbs)
        ->toContain('<ULink')
        ->not->toContain('<a v-if="item.href');
});

test('the floating form system covers the approved controls and retires flat wrappers', function (): void {
    $controls = [
        'FormAsyncSelect',
        'FormCheckbox',
        'FormCurrencyInput',
        'FormField',
        'FormFileUpload',
        'FormInput',
        'FormMultiSelect',
        'FormPhoneInput',
        'FormRadioGroup',
        'FormSelect',
        'FormSwitch',
        'FormTextarea',
    ];

    foreach ($controls as $control) {
        $directory = resource_path("js/components/Ui/{$control}");

        expect(File::exists("{$directory}/{$control}.vue"))->toBeTrue("Missing {$control} component")
            ->and(File::exists("{$directory}/index.ts"))->toBeTrue("Missing {$control} public API")
            ->and(File::exists("{$directory}/types.ts"))->toBeTrue("Missing {$control} types");
    }

    foreach ([
        'AsyncCrmSelect.vue',
        'FormCurrencyInput.vue',
        'FormField.vue',
        'FormInput.vue',
        'FormMultiSelect.vue',
        'FormPhoneInput.vue',
        'FormSelect.vue',
        'FormTextarea.vue',
    ] as $retiredWrapper) {
        expect(File::exists(resource_path("js/components/{$retiredWrapper}")))->toBeFalse("Retired wrapper returned: {$retiredWrapper}");
    }

    $field = File::get(resource_path('js/components/Ui/FormField/FormField.vue'));
    $fieldState = File::get(resource_path('js/components/Ui/FormField/useFormField.ts'));
    $forms = File::get(resource_path('css/theme/forms.css'));
    $migrationTargets = File::get(resource_path('js/components/Ui/formMigrationTargets.ts'));

    expect($field)
        ->toContain(':data-disabled="disabled"')
        ->toContain(':data-filled="filled"')
        ->toContain(':data-invalid="Boolean(errorMessage)"')
        ->toContain(':data-loading="loading"')
        ->toContain(':data-readonly="readonly"')
        ->toContain("messageTone === 'error' ? 'alert' : 'status'")
        ->and($fieldState)
        ->toContain('useId()')
        ->toContain('ariaDescribedby: message.value ? messageId.value : undefined')
        ->toContain('ariaInvalid: errorMessage.value ? ("true" as const) : undefined')
        ->and($forms)
        ->toContain(':has(input:autofill)')
        ->toContain(':has(input:-webkit-autofill)')
        ->toContain('[data-filled="true"]')
        ->toContain('[data-disabled="true"]')
        ->toContain('[data-readonly="true"]')
        ->toContain('[data-loading="true"]')
        ->and($migrationTargets)
        ->toContain('"components/FormField.vue": "components/Ui/FormField"')
        ->toContain('"components/AsyncCrmSelect.vue": "components/AsyncCrmSelect using components/Ui/FormAsyncSelect"')
        ->toContain('"native input[type=file]": "components/Ui/FormFileUpload"')
        ->toContain('"native input[type=datetime-local]": "components/Ui/FormInput type=datetime-local"');
});

test('Argon specimens cover foundation, primitive, and form states', function (): void {
    $foundation = File::get(resource_path('js/pages/Development/Argon/Components/FoundationSpecimens.vue'));
    $primitives = File::get(resource_path('js/pages/Development/Argon/Components/PrimitiveSpecimens.vue'));
    $forms = File::get(resource_path('js/pages/Development/Argon/Components/FormSpecimens.vue'));

    expect($foundation)
        ->toContain('bg-primary')
        ->toContain('bg-secondary')
        ->toContain('bg-success')
        ->toContain('bg-error')
        ->and($primitives)
        ->toContain('v-for="tone in uiTones"')
        ->toContain('label="Loading" loading')
        ->toContain('<EmptyState')
        ->toContain('<Progress')
        ->and($forms)
        ->toContain('label="Empty text"')
        ->toContain('label="Filled or restored"')
        ->toContain('error="Review this value."')
        ->toContain('disabled label="Disabled"')
        ->toContain('label="Readonly"')
        ->toContain('label="Loading search"')
        ->toContain('<FormAsyncSelect')
        ->toContain('<FormFileUpload')
        ->toContain('<FormRadioGroup')
        ->toContain('<FormSwitch');
});
