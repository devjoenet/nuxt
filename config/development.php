<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureUserIsActive;

return [
    'enabled' => true,
    'middleware' => ['web', 'auth', EnsureUserIsActive::class, 'verified'],
    'user_table' => 'users',
];
