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
class DevelopmentTaskRejected extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $taskTitle,
        public string $reason,
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
            ->subject("Development request not planned: {$this->taskTitle}")
            ->greeting('Your development request was reviewed.')
            ->line('It was not added to the roadmap for the following reason:')
            ->line($this->reason);
    }

    /** @return array<string, string> */
    public function viaQueues(): array
    {
        return ['mail' => 'mail'];
    }
}
