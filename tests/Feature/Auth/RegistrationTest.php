<?php

declare(strict_types=1);

test('public registration is disabled after invite-based access management is available', function (): void {
    $response = $this->get('/register');

    $response->assertNotFound();
    $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertNotFound();
});
