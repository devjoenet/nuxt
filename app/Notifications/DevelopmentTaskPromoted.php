<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Attributes\Tries;

#[Backoff([60, 300, 900])]
#[Timeout(60)]
#[Tries(3)]
class DevelopmentTaskPromoted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $taskUuid,
        public string $taskTitle,
    ) {
        $this->onQueue('mail');
        $this->afterCommit();
    }

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Added to the roadmap: {$this->taskTitle}")
            ->greeting('Your development request has been planned.')
            ->line('The review is complete and the request is now on the development roadmap.')
            ->action('View request', route('development.tasks.show', $this->taskUuid));
    }

    /** @return array<string, string> */
    public function viaQueues(): array
    {
        return ['mail' => 'mail'];
    }
}
