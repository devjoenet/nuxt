<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\FollowUpTask;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Attributes\Tries;
use LogicException;

#[Backoff([60, 300, 900])]
#[Timeout(60)]
#[Tries(3)]
class FollowUpTaskReminder extends Notification implements ShouldQueue
{
    use Queueable;

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
        $lead = $this->task->lead;

        if ($lead === null) {
            throw new LogicException("Cannot send a follow-up reminder for task `{$this->task->uuid}` without a lead.");
        }

        $contact = $lead->contact;

        return (new MailMessage)
            ->subject('Follow up with '.$contact->name)
            ->greeting('A CRM follow-up is due.')
            ->line('Task: '.$this->task->title)
            ->line('Lead: '.$contact->name)
            ->line('Email: '.$contact->email)
            ->line('Due: '.$this->task->due_at->toDayDateTimeString())
            ->line($this->task->description ?? 'Review the lead and make the next contact attempt.');
    }
}
