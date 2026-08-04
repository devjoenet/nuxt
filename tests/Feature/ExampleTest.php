<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;

test('returns a successful response', function (): void {
    $this->withoutVite();

    $response = $this->get('/');

    $response
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Welcome')
            ->etc());
});
