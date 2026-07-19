<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\Attributes\UniqueFor;
use Illuminate\Support\Str;

#[Backoff([60, 300, 900])]
#[Tries(3)]
#[UniqueFor(3600)]
class QueueFailureDetected extends Notification implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $connectionName,
        public string $queueName,
        public string $jobName,
        public string $jobId,
        public string $exceptionMessage,
    ) {
        $this->onQueue('mail');
    }

    public function uniqueId(): string
    {
        return 'queue-failure:'.$this->jobId;
    }

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->error()
            ->subject('Southeast Code queue job failed')
            ->greeting('A background job needs attention.')
            ->line('Job: '.$this->jobName)
            ->line(sprintf('Queue: %s (%s)', $this->queueName, $this->connectionName))
            ->line('Job ID: '.$this->jobId)
            ->line('Exception: '.Str::limit($this->exceptionMessage, 500));
    }

    /** @return array<string, string> */
    public function viaQueues(): array
    {
        return ['mail' => 'mail'];
    }

    /** @return array<int, string> */
    public function tags(): array
    {
        return ['notifications', 'operations', 'queue-failure'];
    }
}
