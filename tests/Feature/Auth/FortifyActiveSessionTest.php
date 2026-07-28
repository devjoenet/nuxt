<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureUserIsActive;
use App\Models\User;

dataset('authenticated Fortify security routes', [
    'password confirmation screen' => ['get', 'password.confirm'],
    'password confirmation store' => ['post', 'password.confirm.store'],
    'confirmed password status' => ['get', 'password.confirmation'],
    'confirm two-factor authentication' => ['post', 'two-factor.confirm'],
    'enable two-factor authentication' => ['post', 'two-factor.enable'],
    'disable two-factor authentication' => ['delete', 'two-factor.disable'],
    'two-factor QR code' => ['get', 'two-factor.qr-code'],
    'two-factor recovery codes' => ['get', 'two-factor.recovery-codes'],
    'regenerate two-factor recovery codes' => ['post', 'two-factor.regenerate-recovery-codes'],
    'two-factor secret key' => ['get', 'two-factor.secret-key'],
]);

test('every authenticated Fortify security route checks active account state', function (): void {
    foreach ([
        'password.confirm',
        'password.confirm.store',
        'password.confirmation',
        'two-factor.confirm',
        'two-factor.enable',
        'two-factor.disable',
        'two-factor.qr-code',
        'two-factor.recovery-codes',
        'two-factor.regenerate-recovery-codes',
        'two-factor.secret-key',
    ] as $routeName) {
        $route = app('router')->getRoutes()->getByName($routeName);

        expect($route)->not->toBeNull()
            ->and($route->middleware())->toContain(EnsureUserIsActive::class);
    }
});

test('deactivated sessions are ended before Fortify security endpoints run', function (string $method, string $routeName): void {
    $user = User::factory()->withTwoFactorAuthentication()->create([
        'is_active' => false,
        'deactivated_at' => now(),
    ]);
    $originalTwoFactorState = $user->only([
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ]);

    $this->actingAs($user);
    $initialSessionId = session()->getId();
    $response = $this->{$method}(route($routeName));

    $response->assertRedirect(route('login'))->assertSessionHasErrors('email');
    $this->assertGuest();
    expect(session()->getId())->not->toBe($initialSessionId)
        ->and($user->refresh()->only([
            'two_factor_secret',
            'two_factor_recovery_codes',
            'two_factor_confirmed_at',
        ]))->toEqual($originalTwoFactorState);
})->with('authenticated Fortify security routes');
