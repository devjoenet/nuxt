<?php

declare(strict_types=1);

use Pest\Rector\Rules\RemoveDebugExpectationsRector;
use Pest\Rector\Rules\RemoveOnlyRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([__DIR__.'/tests'])
    ->withRules([
        RemoveOnlyRector::class,
        RemoveDebugExpectationsRector::class,
    ]);
