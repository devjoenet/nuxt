<?php

declare(strict_types=1);

namespace App\Queries;

use Illuminate\Support\Facades\File;
use JsonException;
use UnexpectedValueException;

final class ArgonGalleryManifestQuery
{
    /**
     * @return array{
     *     summary: array{
     *         product: string,
     *         version: string,
     *         snapshot: string,
     *         total: int,
     *         available: int,
     *         empty: int,
     *         families: int,
     *         routable: int,
     *         complete: bool,
     *         source_publication: string,
     *         application_use: string
     *     },
     *     family_counts: array<string, int>,
     *     examples: array<int, array<string, mixed>>
     * }
     *
     * @throws JsonException
     */
    public function get(): array
    {
        $manifest = $this->manifest();
        $examples = $this->examples($manifest);
        $familyCounts = $this->integerMap($manifest, 'family_counts');
        $statusCounts = $this->integerMap($manifest, 'example_source_status_counts');
        $source = $this->arrayValue($manifest, 'source');
        $authorization = $this->arrayValue($manifest, 'authorization');
        $expectedTotal = $this->integerValue($source, 'example_count');

        return [
            'summary' => [
                'product' => $this->stringValue($source, 'product'),
                'version' => $this->stringValue($source, 'version'),
                'snapshot' => $this->stringValue($source, 'snapshot_sha256'),
                'total' => $expectedTotal,
                'available' => $statusCounts['available'] ?? 0,
                'empty' => $statusCounts['empty'] ?? 0,
                'families' => count($familyCounts),
                'routable' => count($examples),
                'complete' => count($examples) === $expectedTotal
                    && array_sum($familyCounts) === $expectedTotal
                    && array_sum($statusCounts) === $expectedTotal,
                'source_publication' => $this->stringValue($authorization, 'source_publication'),
                'application_use' => $this->stringValue($authorization, 'application_use'),
            ],
            'family_counts' => $familyCounts,
            'examples' => $examples,
        ];
    }

    /** @return array<string, mixed>|null */
    public function find(string $id): ?array
    {
        foreach ($this->get()['examples'] as $example) {
            if ($example['id'] === $id) {
                return $example;
            }
        }

        return null;
    }

    /** @return array<string, mixed> */
    private function manifest(): array
    {
        $manifest = json_decode(
            File::get(resource_path('argon/source-manifest.json')),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        if (! is_array($manifest)) {
            throw new UnexpectedValueException('The Argon source manifest must decode to an array.');
        }

        return $manifest;
    }

    /**
     * @param  array<string, mixed>  $manifest
     * @return array<int, array<string, mixed>>
     */
    private function examples(array $manifest): array
    {
        $examples = $this->arrayValue($manifest, 'examples');

        return array_map(function (mixed $example): array {
            if (! is_array($example)) {
                throw new UnexpectedValueException('Every Argon gallery example must be an array.');
            }

            return $this->normalizeExample($example);
        }, array_values($examples));
    }

    /**
     * @param  array<string, mixed>  $example
     * @return array<string, mixed>
     */
    private function normalizeExample(array $example): array
    {
        $sourceStatus = $this->stringValue($example, 'source_status');

        return [
            'id' => $this->stringValue($example, 'id'),
            'title' => $this->stringValue($example, 'title'),
            'family' => $this->stringValue($example, 'family'),
            'source_path' => $this->stringValue($example, 'source_path'),
            'source_status' => $sourceStatus,
            'layout' => $this->stringValue($example, 'layout'),
            'adaptation_state' => $this->stringValue($example, 'adaptation_state'),
            'planned_vue_destination' => $this->stringValue($example, 'planned_vue_destination'),
            'behaviors' => $this->stringList($example, 'behaviors'),
            'dependencies' => $this->stringList($example, 'dependencies'),
            'source_note' => $sourceStatus === 'empty'
                ? 'The licensed source file is empty; the gallery preserves its manifest record and route without inventing missing design content.'
                : 'The licensed source remains outside Git and is available only as authorized implementation reference.',
            'divergence_note' => 'Task 9 exposes manifest metadata and an inert reference route. The visual Vue port is intentionally deferred to the family implementation task.',
            'consuming_pages' => [],
        ];
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private function arrayValue(array $values, string $key): array
    {
        $value = $values[$key] ?? null;

        if (! is_array($value)) {
            throw new UnexpectedValueException("The Argon manifest {$key} value must be an array.");
        }

        return $value;
    }

    /** @param array<string, mixed> $values */
    private function stringValue(array $values, string $key): string
    {
        $value = $values[$key] ?? null;

        if (! is_string($value)) {
            throw new UnexpectedValueException("The Argon manifest {$key} value must be a string.");
        }

        return $value;
    }

    /** @param array<string, mixed> $values */
    private function integerValue(array $values, string $key): int
    {
        $value = $values[$key] ?? null;

        if (! is_int($value)) {
            throw new UnexpectedValueException("The Argon manifest {$key} value must be an integer.");
        }

        return $value;
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<int, string>
     */
    private function stringList(array $values, string $key): array
    {
        $list = $this->arrayValue($values, $key);

        foreach ($list as $value) {
            if (! is_string($value)) {
                throw new UnexpectedValueException("Every Argon manifest {$key} value must be a string.");
            }
        }

        return array_values($list);
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, int>
     */
    private function integerMap(array $values, string $key): array
    {
        $map = $this->arrayValue($values, $key);

        foreach ($map as $value) {
            if (! is_int($value)) {
                throw new UnexpectedValueException("Every Argon manifest {$key} entry must map a string to an integer.");
            }
        }

        return $map;
    }
}
