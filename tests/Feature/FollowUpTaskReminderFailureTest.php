<?php

declare(strict_types=1);

use App\Jobs\SendFollowUpTaskReminder;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Support\Facades\Log;

test('reminder failure logs contain safe correlation metadata without exception text', function (): void {
    Log::spy();
    $queuedJob = Mockery::mock(Job::class);
    $queuedJob->allows('uuid')->andReturn('queue-job-uuid');
    $reminder = new SendFollowUpTaskReminder('task-uuid', '2026-10-20');
    $reminder->setJob($queuedJob);

    $reminder->failed(new RuntimeException('SMTP rejected customer@example.com with secret-token-123.'));

    Log::shouldHaveReceived('error')->once()->with('Follow-up reminder job failed.', [
        'task_uuid' => 'task-uuid',
        'reminder_date' => '2026-10-20',
        'correlation_id' => 'follow-up-reminder:task-uuid:2026-10-20',
        'job_id' => 'queue-job-uuid',
        'exception_class' => RuntimeException::class,
    ]);
});

test('reminder failure logs tolerate a missing exception and queued job', function (): void {
    Log::spy();
    $reminder = new SendFollowUpTaskReminder('task-uuid', '2026-10-20');

    $reminder->failed(null);

    Log::shouldHaveReceived('error')->once()->with('Follow-up reminder job failed.', [
        'task_uuid' => 'task-uuid',
        'reminder_date' => '2026-10-20',
        'correlation_id' => 'follow-up-reminder:task-uuid:2026-10-20',
        'job_id' => null,
        'exception_class' => null,
    ]);
});
