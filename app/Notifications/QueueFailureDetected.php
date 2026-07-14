<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class QueueFailureDetected extends Notification implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [60, 300, 900];

    public int $uniqueFor = 3600;

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
