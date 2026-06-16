<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
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

test('welcome page contains Southeast Code landing content', function (): void {
    $source = File::get(resource_path('js/pages/Welcome.vue'));

    expect($source)
        ->toContain('Southeast Code')
        ->toContain('Custom software for work that refuses to fit')
        ->toContain('Software shaped around the operation.')
        ->toContain('Ready to replace the workaround?');
});
