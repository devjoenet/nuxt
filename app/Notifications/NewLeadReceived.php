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

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [60, 300, 900];

    public int $timeout = 60;

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
