<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use SoutheastCode\Crm\FollowUps\Models\FollowUpTask;

class FollowUpTaskReminder extends Notification
{
    public function __construct(public FollowUpTask $task) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /** @return array<int, string> */
    public function tags(): array
    {
        return ['crm', 'notifications', 'follow-up-reminder:'.$this->task->uuid];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $lead = $this->task->lead;
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
