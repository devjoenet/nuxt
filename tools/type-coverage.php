<?php

declare(strict_types=1);

// Type Coverage 4 uses this marker to disable forks. Its shared PHP cache can
// corrupt after the cache lock times out; remove this workaround after upgrading.
$_ENV['__PEST_PLUGIN_ENV'] = '1';

// Identify the delegated executable before Composer loads PAO's CLI detector.
$_SERVER['argv'][0] = __DIR__.'/../vendor/bin/pest';

require __DIR__.'/../vendor/bin/pest';
