<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\Timeout;

#[Backoff([60, 300, 900])]
#[Timeout(60)]
class NewLeadReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Lead $lead)
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
        return ['crm', 'notifications', 'lead:'.$this->lead->uuid];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $contact = $this->lead->contact;

        $message = (new MailMessage)
            ->subject('New website inquiry from '.$contact->name)
            ->greeting('A new website inquiry is ready for review.')
            ->line('Name: '.$contact->name)
            ->line('Email: '.$contact->email);

        if ($this->lead->organization !== null) {
            $message->line('Organization: '.$this->lead->organization->name);
        }

        return $message
            ->line('Project details: '.$this->lead->project_details)
            ->line('A follow-up task is due the next business day.');
    }
}
