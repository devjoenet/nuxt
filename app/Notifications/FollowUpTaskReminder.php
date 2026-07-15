<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\FollowUpTask;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FollowUpTaskReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [60, 300, 900];

    public int $timeout = 60;

    public function __construct(public FollowUpTask $task)
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

    /** @return array<int, string> */
    public function tags(): array
    {
        return ['crm', 'notifications', 'follow-up-reminder:'.$this->task->uuid];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Follow up with '.$this->task->lead->contact->name)
            ->greeting('A CRM follow-up is due.')
            ->line('Task: '.$this->task->title)
            ->line('Lead: '.$this->task->lead->contact->name)
            ->line('Email: '.$this->task->lead->contact->email)
            ->line('Due: '.$this->task->due_at->toDayDateTimeString())
            ->line($this->task->description ?? 'Review the lead and make the next contact attempt.');
    }
}
