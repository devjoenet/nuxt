<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;

function verificationNotificationUrl(User $user): string
{
    /** @var VerifyEmail $notification */
    $notification = Notification::sent($user, VerifyEmail::class)->sole();

    return $notification->toMail($user)->actionUrl;
}

test('sends a verification notification with a valid application URL', function (): void {
    $user = User::factory()->create([
        'email_verified_at' => null,
    ]);
    Notification::fake();

    $this->actingAs($user)
        ->post('email/verification-notification')
        ->assertRedirect('/');

    Notification::assertSentTo($user, VerifyEmail::class);

    $url = verificationNotificationUrl($user);
    $path = parse_url($url, PHP_URL_PATH);
    $segments = explode('/', mb_trim((string) $path, '/'));
    $query = parse_url($url, PHP_URL_QUERY);
    parse_str((string) $query, $parameters);

    expect(parse_url($url, PHP_URL_HOST))->toBe(parse_url((string) config('app.url'), PHP_URL_HOST))
        ->and($path)->toBe(parse_url(route('verification.verify', [
            'id' => $user->getKey(),
            'hash' => sha1((string) $user->email),
        ]), PHP_URL_PATH))
        ->and($segments[0])->toBe('verify-email')
        ->and($segments[1])->toBe((string) $user->getKey())
        ->and($segments[2])->toBe(sha1((string) $user->email))
        ->and((int) ($parameters['expires'] ?? 0))->toBeGreaterThan(now()->timestamp)
        ->and($parameters['signature'] ?? null)->not->toBeNull();
});

test('does not send verification notification if email is verified', function (): void {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);
    Notification::fake();

    $this->actingAs($user)
        ->post('email/verification-notification')
        ->assertRedirect(route('dashboard', absolute: false));

    Notification::assertNothingSent();
});

test('verification notification URL verifies the target user', function (): void {
    $user = User::factory()->unverified()->create();
    Notification::fake();

    $this->actingAs($user)->post('email/verification-notification');
    $url = verificationNotificationUrl($user);

    $this->actingAs($user)->get($url)->assertRedirect(route('dashboard', absolute: false).'?verified=1');

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

test('verification notification URL rejects a guest and wrong user', function (): void {
    $target = User::factory()->unverified()->create();
    $wrongUser = User::factory()->create();
    Notification::fake();

    $this->actingAs($target)->post('email/verification-notification');
    $url = verificationNotificationUrl($target);

    auth()->logout();
    $this->get($url)->assertRedirect(route('login'));
    $this->actingAs($wrongUser)->get($url)->assertForbidden();
    expect($target->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('verification notification URL rejects tampering and expiry', function (): void {
    $user = User::factory()->unverified()->create();
    Notification::fake();

    $this->actingAs($user)->post('email/verification-notification');
    $url = verificationNotificationUrl($user);
    $tamperedUrl = str_replace(sha1((string) $user->email), 'tampered', $url);

    $this->actingAs($user)->get($tamperedUrl)->assertForbidden();

    $this->travelTo(now()->addMinutes((int) config('auth.verification.expire', 60) + 1));
    $this->actingAs($user)->get($url)->assertForbidden();
    $this->travelBack();
});
