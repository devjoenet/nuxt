<?php

declare(strict_types=1);

test('maintained landing assets stay within their responsive dimensions and budgets', function (): void {
    $variants = [
        'light' => [640, 960, 1264],
        'dark' => [640, 960, 1536],
    ];

    foreach ($variants as $theme => $widths) {
        foreach (['avif' => 250, 'webp' => 400] as $format => $budgetKilobytes) {
            foreach ($widths as $width) {
                $path = resource_path("img/landing/hero/savannah-river-{$theme}-{$width}.{$format}");
                $dimensions = getimagesize($path);

                if ($dimensions === false) {
                    throw new RuntimeException("Unable to read image dimensions for {$path}.");
                }

                expect(is_file($path))->toBeTrue()
                    ->and(filesize($path))->toBeLessThanOrEqual($budgetKilobytes * 1024)
                    ->and($dimensions[0])->toBe($width)
                    ->and($dimensions[1] / $dimensions[0])->toBeLessThan(0.68)
                    ->and($dimensions[1] / $dimensions[0])->toBeGreaterThan(0.64);
            }
        }
    }
});

test('the social card is a dedicated 1200 by 630 asset within budget', function (): void {
    $path = resource_path('img/landing/social/southeast-code-social-card.avif');
    $dimensions = getimagesize($path);

    if ($dimensions === false) {
        throw new RuntimeException("Unable to read image dimensions for {$path}.");
    }

    expect(is_file($path))->toBeTrue()
        ->and(filesize($path))->toBeLessThanOrEqual(250 * 1024)
        ->and($dimensions[0])->toBe(1200)
        ->and($dimensions[1])->toBe(630);
});
