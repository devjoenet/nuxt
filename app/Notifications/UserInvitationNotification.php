<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Attributes\Tries;

use function config;
use function route;

#[Backoff([60, 300, 900])]
#[Timeout(60)]
#[Tries(3)]
class UserInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $token)
    {
        $this->onQueue('mail');
    }

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /** @return array<string, string> */
    public function viaQueues(): array
    {
        return ['mail' => 'mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $broker = config()->string('fortify.passwords', 'users');
        $expiry = config()->integer(sprintf('auth.passwords.%s.expire', $broker), 60);

        return (new MailMessage)
            ->subject('Set up your Southeast Code account')
            ->greeting('Welcome to Southeast Code.')
            ->line('An administrator created a Southeast Code account for you.')
            ->line('Choose your password with the button below, then sign in and verify your email address.')
            ->line(sprintf('This setup link expires in %d minutes.', $expiry))
            ->action('Set up your account', route('password.reset', [
                'token' => $this->token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]));
    }
}
