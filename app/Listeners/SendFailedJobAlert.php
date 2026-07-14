<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Notifications\QueueFailureDetected;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Notification;

class SendFailedJobAlert
{
    public function handle(JobFailed $event): void
    {
        if ($event->job->resolveName() === SendQueuedNotifications::class) {
            return;
        }

        $recipient = config('services.horizon.notification_email');

        if (! is_string($recipient) || $recipient === '') {
            return;
        }

        Notification::route('mail', $recipient)->notify(new QueueFailureDetected(
            connectionName: (string) $event->connectionName,
            queueName: (string) $event->job->getQueue(),
            jobName: (string) $event->job->resolveName(),
            jobId: (string) ($event->job->uuid() ?? $event->job->getJobId()),
            exceptionMessage: $event->exception->getMessage(),
        ));
    }
}
