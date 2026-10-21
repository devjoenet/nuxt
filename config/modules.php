<?php

declare(strict_types=1);

return [
    'enabled' => ['foundation', 'users', 'audit', 'crm', 'dev'],
    // Enable only on fresh, isolated consumers before migrations and seeding.
    'stable_morphs' => env('MODULES_STABLE_MORPHS', false),
];
