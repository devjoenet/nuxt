<?php

declare(strict_types=1);

return [
    'middleware' => ['web', 'auth', App\Http\Middleware\EnsureUserIsActive::class, 'verified'],
    'service_interests' => App\Enums\ServiceInterest::options(),
];
