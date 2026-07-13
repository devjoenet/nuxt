<?php

declare(strict_types=1);

use App\Models\User;

test('two factor credentials are hidden when a user is serialized', function (): void {
    $user = User::factory()->withTwoFactorAuthentication()->create();

    expect($user->toArray())
        ->not->toHaveKeys(['two_factor_secret', 'two_factor_recovery_codes']);
});

test('users are created without two factor authentication by default', function (): void {
    $user = User::factory()->create();

    expect($user)
        ->two_factor_secret->toBeNull()
        ->two_factor_recovery_codes->toBeNull()
        ->two_factor_confirmed_at->toBeNull();
});

test('the two factor factory state creates usable encrypted credentials', function (): void {
    $user = User::factory()->withTwoFactorAuthentication()->create();

    expect(decrypt($user->two_factor_secret))->not->toBeEmpty()
        ->and(json_decode(decrypt($user->two_factor_recovery_codes), true, flags: JSON_THROW_ON_ERROR))
        ->toBeArray()
        ->toHaveCount(2)
        ->and($user->two_factor_confirmed_at)->not->toBeNull();
});
