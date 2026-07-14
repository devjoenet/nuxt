<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\FollowUpTaskStatus;
use App\Jobs\SendFollowUpTaskReminder;
use App\Models\FollowUpTask;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

#[Signature('crm:dispatch-follow-up-reminders')]
#[Description('Dispatch due CRM follow-up reminder jobs.')]
class DispatchFollowUpTaskReminders extends Command
{
    public function handle(): int
    {
        $dispatched = 0;
        $today = now()->startOfDay();

        FollowUpTask::query()
            ->where('status', FollowUpTaskStatus::Open)
            ->where('due_at', '<=', now())
            ->where(function (Builder $query) use ($today): void {
                $query->whereNull('reminder_sent_at')->orWhere('reminder_sent_at', '<', $today);
            })
            ->eachById(function (FollowUpTask $task) use (&$dispatched): void {
                SendFollowUpTaskReminder::dispatch($task->uuid, now()->toDateString());
                $dispatched++;
            });

        $this->info(sprintf('Dispatched %d follow-up reminder job(s).', $dispatched));

        return self::SUCCESS;
    }
}
