<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\FollowUpTaskStatus;
use App\Models\BusinessSetting;
use App\Models\FollowUpTask;
use App\Notifications\FollowUpTaskReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

class SendFollowUpTaskReminder implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [60, 300, 900];

    public int $timeout = 60;

    public int $uniqueFor = 3600;

    public function __construct(
        public string $taskUuid,
        public string $reminderDate,
    ) {
        $this->onQueue('reminders');
    }

    public function handle(): void
    {
        $task = FollowUpTask::query()
            ->with(['lead.contact', 'lead.organization'])
            ->where('uuid', $this->taskUuid)
            ->first();

        if ($task === null || $task->status !== FollowUpTaskStatus::Open) {
            return;
        }

        if ($task->reminder_sent_at?->toDateString() === $this->reminderDate) {
            return;
        }

        $lock = Cache::lock($this->uniqueId(), 120);

        if (! $lock->get()) {
            return;
        }

        try {
            $recipient = BusinessSetting::current()->notification_email;

            if ($recipient === null || $recipient === '') {
                Log::warning('Follow-up reminder skipped because no notification recipient is configured.', [
                    'task_uuid' => $task->uuid,
                ]);

                return;
            }

            Notification::route('mail', $recipient)->notify(new FollowUpTaskReminder($task));

            $task->reminder_sent_at = now();
            $task->saveQuietly();
        } finally {
            $lock->release();
        }
    }

    public function uniqueId(): string
    {
        return sprintf('follow-up-reminder:%s:%s', $this->taskUuid, $this->reminderDate);
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Follow-up reminder job failed.', [
            'task_uuid' => $this->taskUuid,
            'exception' => $exception?->getMessage(),
        ]);
    }

    /** @return array<int, string> */
    public function tags(): array
    {
        return ['crm', 'reminders', 'follow-up-task:'.$this->taskUuid];
    }
}
