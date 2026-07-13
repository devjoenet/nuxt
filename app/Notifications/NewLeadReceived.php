<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewLeadReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Lead $lead) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('New website inquiry from '.$this->lead->contact->name)
            ->greeting('A new website inquiry is ready for review.')
            ->line('Name: '.$this->lead->contact->name)
            ->line('Email: '.$this->lead->contact->email);

        if ($this->lead->organization !== null) {
            $message->line('Organization: '.$this->lead->organization->name);
        }

        return $message
            ->line('Project details: '.$this->lead->project_details)
            ->line('A follow-up task is due the next business day.');
    }
}
