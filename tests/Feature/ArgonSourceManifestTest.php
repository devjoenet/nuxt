<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

/** @return array<string, mixed> */
function argonSourceManifest(): array
{
    $manifest = json_decode(
        File::get(resource_path('argon/source-manifest.json')),
        true,
        512,
        JSON_THROW_ON_ERROR,
    );

    expect($manifest)->toBeArray();

    return $manifest;
}

/**
 * @param  array<int, array<string, mixed>>  $records
 * @return array<string, int>
 */
function argonManifestCountsBy(array $records, string $key): array
{
    $counts = [];

    foreach ($records as $record) {
        $value = $record[$key] ?? null;

        expect($value)->toBeString();

        $counts[$value] = ($counts[$value] ?? 0) + 1;
    }

    ksort($counts, SORT_STRING);

    return $counts;
}

test('the Argon source manifest records the approved licensed snapshot', function () {
    $manifest = argonSourceManifest();

    expect($manifest)
        ->schema_version->toBe(1)
        ->source->toMatchArray([
            'product' => 'Argon Dashboard 3 Pro',
            'package_name' => 'argon-dashboard-pro',
            'version' => '2.1.0',
            'package_license' => 'Premium',
            'source_root' => 'argon-dashboard-3-pro',
            'snapshot_sha256' => '6779758b456a8ee90fb5d617edb698e59a4f1f597a9dcb2b8c92dd3c469c34dc',
            'file_count' => 402,
            'example_count' => 69,
            'empty_example_count' => 3,
        ])
        ->authorization->official_license_url->toBe('https://www.creative-tim.com/license')
        ->authorization->licensing_questions->toBe('docs/development/argon-source-licensing.md')
        ->classification_definitions->adaptation_state->toHaveKeys([
            'authored',
            'adapted',
            'generated',
            'runtime-only',
            'excluded',
        ]);
});

test('all licensed examples have stable complete gallery mappings', function () {
    $manifest = argonSourceManifest();
    $examples = $manifest['examples'];
    $files = $manifest['files'];
    $dependencies = $manifest['dependencies'];

    expect($examples)->toBeArray()->toHaveCount(69)
        ->and($manifest['family_counts'])->toBe([
            'account' => 4,
            'applications' => 5,
            'authentication' => 17,
            'dashboards' => 5,
            'ecommerce' => 8,
            'general' => 22,
            'projects' => 3,
            'team' => 5,
        ]);

    $exampleIds = array_column($examples, 'id');
    $sourcePaths = array_column($examples, 'source_path');
    $destinations = array_column($examples, 'planned_vue_destination');
    $filePaths = array_column($files, 'path');
    $dependencyIds = array_column($dependencies, 'id');
    $htmlPaths = array_values(array_column(array_filter(
        $files,
        static fn (array $file): bool => $file['kind'] === 'html-example',
    ), 'path'));

    expect($exampleIds)->toHaveCount(count(array_unique($exampleIds)))
        ->and($sourcePaths)->toHaveCount(count(array_unique($sourcePaths)))
        ->and($destinations)->toHaveCount(count(array_unique($destinations)))
        ->and($sourcePaths)->toBe($htmlPaths);

    foreach ($examples as $example) {
        expect($example)
            ->id->toMatch('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
            ->family->toBeIn(array_keys($manifest['family_counts']))
            ->source_path->toEndWith('.html')
            ->source_status->toBeIn(['available', 'empty'])
            ->title->not->toBeEmpty()
            ->layout->not->toBeEmpty()
            ->assets->toBeArray()
            ->external_assets->toBeArray()
            ->plugins->toBeArray()
            ->dependencies->toBeArray()
            ->behaviors->toBeArray()
            ->planned_vue_destination->toMatch(
                '~^resources/js/pages/Admin/ArgonGallery/Examples/[A-Z][A-Za-z0-9]*(?:/[A-Z][A-Za-z0-9]*)+\.vue$~',
            )
            ->adaptation_state->toBe('adapted')
            ->implementation_state->toBeIn(['implemented', 'planned'])
            ->divergence_note->toBeString()->not->toBeEmpty();

        if ($example['source_status'] === 'available') {
            expect($example)
                ->document_title->toBeString()->not->toBeEmpty()
                ->assets->not->toBeEmpty();
        } else {
            expect($example)
                ->document_title->toBeNull()
                ->layout->toBe('unavailable-empty-source')
                ->assets->toBeEmpty()
                ->plugins->toBeEmpty()
                ->behaviors->toBe(['source-content-missing']);
        }

        expect(array_diff($example['assets'], $filePaths))->toBeEmpty()
            ->and(array_diff($example['plugins'], $dependencyIds))->toBeEmpty()
            ->and(array_diff($example['dependencies'], $dependencyIds))->toBeEmpty();
    }

    expect($manifest['example_source_status_counts'])->toBe([
        'available' => 66,
        'empty' => 3,
    ]);
});

test('the complete source inventory has explicit provenance and distribution decisions', function () {
    $manifest = argonSourceManifest();
    $files = $manifest['files'];
    $paths = array_column($files, 'path');
    $adaptationStates = array_keys($manifest['classification_definitions']['adaptation_state']);
    $provenanceValues = array_keys($manifest['classification_definitions']['provenance']);
    $distributionValues = array_keys($manifest['classification_definitions']['distribution']);
    $snapshotParts = [];

    expect($files)->toHaveCount(402)
        ->and($paths)->toHaveCount(count(array_unique($paths)))
        ->and($manifest['file_kind_counts'])->toHaveKeys([
            'font',
            'html-example',
            'icon',
            'image',
            'javascript-plugin',
            'logo',
            'sass',
            'texture',
        ]);

    foreach ($files as $file) {
        expect($file)
            ->path->toBeString()->not->toBeEmpty()
            ->kind->toBeString()->not->toBeEmpty()
            ->bytes->toBeInt()->toBeGreaterThanOrEqual(0)
            ->sha256->toMatch('/^[a-f0-9]{64}$/')
            ->provenance->toBeIn($provenanceValues)
            ->adaptation_state->toBeIn($adaptationStates)
            ->distribution->toBeIn($distributionValues)
            ->referenced_by->toBeArray();

        $snapshotParts[] = "{$file['path']}\0{$file['sha256']}";
    }

    expect(hash('sha256', implode("\n", $snapshotParts)))
        ->toBe($manifest['source']['snapshot_sha256'])
        ->toBe('6779758b456a8ee90fb5d617edb698e59a4f1f597a9dcb2b8c92dd3c469c34dc')
        ->and($manifest['file_kind_counts'])->toBe(argonManifestCountsBy($files, 'kind'))
        ->and($manifest['adaptation_state_counts'])->toBe(argonManifestCountsBy($files, 'adaptation_state'))
        ->and($manifest['provenance_counts'])->toBe(argonManifestCountsBy($files, 'provenance'))
        ->and($manifest['distribution_counts'])->toBe(argonManifestCountsBy($files, 'distribution'));
});

test('duplicate source files are detected and cross referenced', function () {
    $manifest = argonSourceManifest();
    $files = $manifest['files'];
    $duplicateGroups = $manifest['duplicate_groups'];
    $pathsByHash = [];

    foreach ($files as $file) {
        $pathsByHash[$file['sha256']][] = $file['path'];
    }

    $pathsByHash = array_filter($pathsByHash, static fn (array $paths): bool => count($paths) > 1);
    ksort($pathsByHash, SORT_STRING);

    expect($duplicateGroups)->toHaveCount(count($pathsByHash));

    foreach ($duplicateGroups as $duplicateGroup) {
        expect($duplicateGroup)
            ->id->toMatch('/^duplicate-[0-9]{3}$/')
            ->sha256->toMatch('/^[a-f0-9]{64}$/')
            ->paths->toHaveCount(count($pathsByHash[$duplicateGroup['sha256']]));

        foreach ($duplicateGroup['paths'] as $path) {
            $file = array_values(array_filter(
                $files,
                static fn (array $candidate): bool => $candidate['path'] === $path,
            ))[0];

            expect($file['duplicate_group'])->toBe($duplicateGroup['id']);
        }
    }
});

test('every example dependency has provenance and a recorded adaptation decision', function () {
    $manifest = argonSourceManifest();
    $dependencies = $manifest['dependencies'];
    $dependencyIds = array_column($dependencies, 'id');
    $filePaths = array_column($manifest['files'], 'path');
    $exampleIds = array_column($manifest['examples'], 'id');

    expect($dependencyIds)->toHaveCount(count(array_unique($dependencyIds)));

    foreach ($dependencies as $dependency) {
        expect($dependency)
            ->id->toBeString()->not->toBeEmpty()
            ->kind->toBeString()->not->toBeEmpty()
            ->license->toBeString()->not->toBeEmpty()
            ->provenance->toBeString()->not->toBeEmpty()
            ->distribution->toBeString()->not->toBeEmpty()
            ->decision->toBeString()->not->toBeEmpty()
            ->source_files->toBeArray()
            ->source_urls->toBeArray()
            ->required_by->toBeArray();

        expect(array_diff($dependency['source_files'], $filePaths))->toBeEmpty()
            ->and(array_diff($dependency['required_by'], $exampleIds))->toBeEmpty()
            ->and($dependency['source_files'] !== [] || $dependency['source_urls'] !== [])->toBeTrue();
    }
});

test('the committed manifest matches the available licensed source checkout', function () {
    $configuredSourcePath = getenv('ARGON_SOURCE_PATH');
    $userHome = getenv('HOME');
    $sourcePath = is_string($configuredSourcePath) && $configuredSourcePath !== ''
        ? $configuredSourcePath
        : (is_string($userHome) ? "{$userHome}/code/argon-dashboard-3-pro" : '');
    $sourceRoot = realpath($sourcePath);

    if ($sourceRoot === false) {
        $this->markTestSkipped('The licensed Argon source is not available in this environment.');
    }

    $this->artisan('argon:generate-source-manifest', [
        'source' => $sourceRoot,
        '--check' => true,
    ])->assertSuccessful();
});
