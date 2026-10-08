<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

test('page cards expose the application style and color variant contract', function (): void {
    $appearance = File::get(resource_path('js/lib/cardAppearance.ts'));
    $pageCard = File::get(resource_path('js/components/AppPageCard/AppPageCard.vue'));

    expect($appearance)
        ->toContain('export const cardVariants = ["primary", "secondary", "muted", "neutral", "info", "success", "warning", "error"] as const;')
        ->toContain('export type CardStyle = "solid" | "outline" | "soft" | "subtle";')
        ->toContain('export type PageCardStyle = CardStyle | "ghost" | "naked";')
        ->and($pageCard)
        ->toContain('cn("app-card", cardAppearanceClass(props.style, props.variant), props.class)')
        ->toContain('style: "subtle"')
        ->toContain('variant: "neutral"')
        ->toContain(':variant="props.style"');
});

test('floating labels mask their borders with the active card surface', function (): void {
    $forms = File::get(resource_path('css/theme/forms.css'));

    expect($forms)
        ->not->toContain('--form-field-label-background: transparent;')
        ->toMatch('/\.form-field__shell\s*\{[^}]*--form-field-neutral-color: var\(--ui-text-muted\);/s')
        ->toMatch('/\.form-field__shell\s*\{[^}]*border: 1px solid var\(--form-field-neutral-color\);/s')
        ->toMatch('/\.form-field__shell\s*\{[^}]*background-color: transparent;/s')
        ->toMatch('/\.form-field__shell\[data-variant="filled"\]\s*\{[^}]*border-bottom-color: var\(--form-field-neutral-color\);/s')
        ->toMatch('/\.form-field__shell\[data-variant="filled"\]\s*\{[^}]*background-color: color-mix\(in oklab, var\(--ui-bg-elevated\) 72%, transparent\);/s')
        ->toMatch('/\.form-field__label\s*\{[^}]*color: var\(--app-text-placeholder\);/s')
        ->toContain('--form-field-label-background: var(--ui-bg);')
        ->toContain('--form-field-label-background: var(--app-card-subtle-background);')
        ->toContain('--form-field-label-background: var(--app-card-solid-background);')
        ->toContain('background-color: var(--form-field-label-background, var(--ui-bg));')
        ->toContain('background-color: var(--form-field-label-background, color-mix(in oklab, var(--ui-bg-elevated) 72%, transparent));');

    foreach ([
        'primary' => '--ui-color-primary',
        'secondary' => '--ui-color-secondary',
        'muted' => '--color-slate',
        'neutral' => '--color-grey',
        'info' => '--ui-color-info',
        'success' => '--ui-color-success',
        'warning' => '--ui-color-warning',
        'error' => '--ui-color-error',
    ] as $variant => $color) {
        expect($forms)
            ->toContain(".app-card[data-card-variant=\"{$variant}\"]")
            ->toContain(".dark .app-card[data-card-variant=\"{$variant}\"]")
            ->toContain("--app-card-subtle-background: var({$color}-100);")
            ->toContain("--app-card-subtle-background: var({$color}-800);")
            ->toContain("--app-card-solid-background: var({$color}-800);")
            ->toContain("--app-card-solid-background: var({$color}-200);");
    }
});

test('neutral subtle cards use the requested light and dark colors', function (): void {
    $appearance = File::get(resource_path('js/lib/cardAppearance.ts'));

    expect($appearance)
        ->toContain('neutral: "border-grey-200 dark:border-grey-700"')
        ->toContain('neutral: "bg-grey-100 dark:bg-grey-800"')
        ->toContain('return cn("border ring-0", cardBackgroundClasses[variant], cardBorderClasses[variant]);');
});

test('application card surfaces use the product card contract', function (): void {
    $rawCardFiles = collect(File::allFiles(resource_path('js')))
        ->filter(fn (SplFileInfo $file): bool => $file->getExtension() === 'vue')
        ->reject(fn (SplFileInfo $file): bool => in_array($file->getRelativePathname(), [
            'components/AppPageCard/AppPageCard.vue',
        ], true))
        ->filter(fn (SplFileInfo $file): bool => Str::contains(File::get($file->getPathname()), ['<UCard', '<UPageCard']))
        ->map(fn (SplFileInfo $file): string => $file->getRelativePathname())
        ->values()
        ->all();

    $staticStyleFiles = collect(File::allFiles(resource_path('js')))
        ->filter(fn (SplFileInfo $file): bool => $file->getExtension() === 'vue')
        ->filter(fn (SplFileInfo $file): bool => preg_match('/<App(?:Page)?Card\b[^>]*\sstyle="/', File::get($file->getPathname())) === 1)
        ->map(fn (SplFileInfo $file): string => $file->getRelativePathname())
        ->values()
        ->all();

    $developmentTaskComponents = collect(File::allFiles(resource_path('js/pages/Development/Tasks/Components')))
        ->filter(fn (SplFileInfo $file): bool => $file->getExtension() === 'vue')
        ->map(fn (SplFileInfo $file): string => File::get($file->getPathname()))
        ->implode("\n");

    expect($rawCardFiles)->toBe([])
        ->and($staticStyleFiles)->toBe([]);

    expect($developmentTaskComponents)
        ->toContain('import { Card } from "@/components/Ui/Card";')
        ->not->toContain('<AppCard');
});
