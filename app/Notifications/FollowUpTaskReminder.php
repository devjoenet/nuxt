<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\FollowUpTask;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FollowUpTaskReminder extends Notification
{
    public function __construct(public FollowUpTask $task) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
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
