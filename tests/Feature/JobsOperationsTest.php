<?php

declare(strict_types=1);

use App\Enums\RoleName;
use App\Jobs\SendFollowUpTaskReminder;
use App\Listeners\SendFailedJobAlert;
use App\Models\BusinessSetting;
use App\Models\User;
use App\Notifications\FollowUpTaskReminder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Contracts\Mail\Factory as MailFactory;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Contracts\Queue\Queue as QueueContract;
use Illuminate\Mail\Markdown;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia as Assert;
use SoutheastCode\Crm\FollowUps\Models\FollowUpTask;
use Spatie\Activitylog\Models\Activity;

beforeEach(function (): void {
    app(RolePermissionSeeder::class)->run();
    $this->withoutVite();
});

function jobsUser(string $role = RoleName::Admin->value): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

test('users with jobs view permission can open the operations page', function (): void {
    $user = jobsUser(RoleName::SalesOps->value);

    $this->actingAs($user)
        ->get(route('admin.jobs.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Admin/Jobs/Index')
            ->has('stats', 3)
            ->has('links', 6));
});

test('users without jobs view permission cannot open the operations page', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('admin.jobs.index'))->assertForbidden();
});

test('pending counts include non-default database queues without requiring Horizon or Redis', function (): void {
    config(['queue.default' => 'database', 'queue.connections.database.after_commit' => false]);
    $queue = Queue::connection('database');
    $queue->push(new SendFollowUpTaskReminder('default-task', now()->toDateString()), '', 'default');
    $queue->push(new SendFollowUpTaskReminder('mail-task', now()->toDateString()), '', 'mail');
    $queue->push(new SendFollowUpTaskReminder('crm-task', now()->toDateString()), '', 'crm');
    $queue->later(3600, new SendFollowUpTaskReminder('delayed-task', now()->toDateString()), '', 'mail');
    $queue->push(new SendFollowUpTaskReminder('reserved-task', now()->toDateString()), '', 'reminders');
    $queue->pop('reminders');

    $this->actingAs(jobsUser())->get(route('admin.jobs.index'))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('stats.0.label', 'Total pending jobs')
            ->where('stats.0.value', 3)
            ->has('queues', 9)
            ->where('queues.0', ['connection' => 'database', 'name' => 'default', 'pending' => 1])
            ->where('queues.1', ['connection' => 'database', 'name' => 'crm', 'pending' => 1])
            ->where('queues.6', ['connection' => 'database', 'name' => 'mail', 'pending' => 1])
            ->where('queues.8.pending', 0));
});

test('pending counts follow Horizon environment overrides and deduplicate connection queue pairs', function (): void {
    config([
        'queue.default' => 'redis',
        'horizon.defaults' => [
            'primary' => ['connection' => 'redis', 'queue' => ['default', 'mail'], 'maxProcesses' => 1],
            'secondary' => ['connection' => 'other-redis', 'queue' => ['mail'], 'maxProcesses' => 1],
        ],
        'horizon.environments' => ['*' => [
            'primary' => ['queue' => ['crm', 'mail']],
            'duplicate' => ['connection' => 'redis', 'queue' => 'mail,crm', 'maxProcesses' => 1],
            'disabled' => ['connection' => 'redis', 'queue' => ['disabled'], 'maxProcesses' => 0],
        ]],
    ]);
    $primary = Mockery::mock(QueueContract::class);
    $primary->shouldReceive('pendingSize')->once()->with('crm')->andReturn(2);
    $primary->shouldReceive('pendingSize')->once()->with('mail')->andReturn(3);
    $primary->shouldReceive('pendingSize')->once()->with('default')->andReturn(1);
    $secondary = Mockery::mock(QueueContract::class);
    $secondary->shouldReceive('pendingSize')->once()->with('mail')->andReturn(4);
    Queue::shouldReceive('connection')->with('redis')->andReturn($primary);
    Queue::shouldReceive('connection')->with('other-redis')->andReturn($secondary);

    $this->actingAs(jobsUser())->get(route('admin.jobs.index'))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('stats.0.value', 10)
            ->where('queues', [
                ['connection' => 'redis', 'name' => 'crm', 'pending' => 2],
                ['connection' => 'redis', 'name' => 'mail', 'pending' => 3],
                ['connection' => 'other-redis', 'name' => 'mail', 'pending' => 4],
                ['connection' => 'redis', 'name' => 'default', 'pending' => 1],
            ]));
});

test('the Jobs page shows unavailable counts when Redis cannot be reached', function (): void {
    config(['queue.default' => 'redis']);
    Exceptions::fake();
    Queue::shouldReceive('connection')->once()->with('redis')
        ->andThrow(new RuntimeException('Redis unavailable.'));

    $this->actingAs(jobsUser())->get(route('admin.jobs.index'))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('stats.0.value', null)
            ->where('stats.1.value', 0)
            ->has('links', 6)
            ->where('queues.0.pending', null));

    Exceptions::assertReported(RuntimeException::class);
});

test('an unavailable queue connection preserves readable counts without presenting a partial total', function (): void {
    config([
        'queue.default' => 'redis',
        'horizon.defaults' => [],
        'horizon.environments' => ['testing' => [
            'primary' => ['connection' => 'redis', 'queue' => ['default'], 'maxProcesses' => 1],
            'mail' => ['connection' => 'mail-redis', 'queue' => ['mail', 'notifications'], 'maxProcesses' => 1],
        ]],
    ]);
    Exceptions::fake();
    $primary = Mockery::mock(QueueContract::class);
    $primary->shouldReceive('pendingSize')->once()->with('default')->andReturn(5);
    Queue::shouldReceive('connection')->once()->with('redis')->andReturn($primary);
    Queue::shouldReceive('connection')->once()->with('mail-redis')
        ->andThrow(new RuntimeException('Mail Redis unavailable.'));

    $this->actingAs(jobsUser())->get(route('admin.jobs.index'))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('stats.0.value', null)
            ->where('queues', [
                ['connection' => 'redis', 'name' => 'default', 'pending' => 5],
                ['connection' => 'mail-redis', 'name' => 'mail', 'pending' => null],
                ['connection' => 'mail-redis', 'name' => 'notifications', 'pending' => null],
            ]));

    Exceptions::assertReported(RuntimeException::class);
});

test('the reminder command dispatches due tasks onto the mail queue', function (): void {
    Queue::fake();
    $task = FollowUpTask::factory()->create(['due_at' => now()->subHour()]);

    $this->artisan('crm:dispatch-follow-up-reminders')->assertSuccessful();

    Queue::assertPushed(SendFollowUpTaskReminder::class, fn (SendFollowUpTaskReminder $job): bool => $job->taskUuid === $task->uuid
        && $job->queue === 'mail');
});

test('a follow-up reminder is marked sent once after synchronous mail delivery', function (): void {
    Mail::fake();
    Queue::fake();
    $task = FollowUpTask::factory()->create(['due_at' => now()->subHour()]);
    BusinessSetting::current()->update(['notification_email' => 'ops@example.com']);
    $job = new SendFollowUpTaskReminder($task->uuid, now()->toDateString());

    $job->handle();
    $job->handle();

    Queue::assertNothingPushed();
    expect($job->queue)->toBe('mail')
        ->and(new FollowUpTaskReminder($task))->not->toBeInstanceOf(Illuminate\Contracts\Queue\ShouldQueue::class)
        ->and($task->refresh()->reminder_sent_at)->not->toBeNull();
});

test('a failed mail-channel delivery leaves a follow-up reminder eligible for retry', function (): void {
    $task = FollowUpTask::factory()->create(['due_at' => now()->subHour()]);
    BusinessSetting::current()->update(['notification_email' => 'ops@example.com']);
    $job = new SendFollowUpTaskReminder($task->uuid, now()->toDateString());

    $mailer = Mockery::mock(Mailer::class);
    $mailer->shouldReceive('send')->once()->andThrow(new RuntimeException('Mail delivery failed.'));

    $mailFactory = Mockery::mock(MailFactory::class);
    $mailFactory->shouldReceive('mailer')->once()->with(null)->andReturn($mailer);

    app()->instance(MailChannel::class, new MailChannel($mailFactory, app(Markdown::class)));

    expect(fn (): mixed => $job->handle())->toThrow(RuntimeException::class, 'Mail delivery failed.')
        ->and($task->refresh()->reminder_sent_at)->toBeNull();

    app(ChannelManager::class)->forgetDrivers();
    app()->forgetInstance(MailChannel::class);
    Mail::fake();

    $job->handle();

    expect($task->refresh()->reminder_sent_at)->not->toBeNull();
});

test('follow-up reminder jobs skip ineligible, duplicate, locked, and unconfigured deliveries', function (): void {
    Mail::fake();
    Log::spy();

    (new SendFollowUpTaskReminder('missing-task', now()->toDateString()))->handle();

    $completed = FollowUpTask::factory()->create([
        'status' => 'completed',
        'completed_at' => now(),
    ]);
    (new SendFollowUpTaskReminder($completed->uuid, now()->toDateString()))->handle();

    $alreadySent = FollowUpTask::factory()->create([
        'reminder_sent_at' => now(),
    ]);
    (new SendFollowUpTaskReminder($alreadySent->uuid, now()->toDateString()))->handle();

    $locked = FollowUpTask::factory()->create();
    $lockedJob = new SendFollowUpTaskReminder($locked->uuid, now()->toDateString());
    $lock = Cache::lock($lockedJob->uniqueId(), 120);
    $lock->get();
    $lockedJob->handle();
    $lock->release();

    $unconfigured = FollowUpTask::factory()->create();
    BusinessSetting::current()->update(['notification_email' => null]);
    $unconfiguredJob = new SendFollowUpTaskReminder($unconfigured->uuid, now()->toDateString());
    $unconfiguredJob->handle();
    $unconfiguredJob->failed(new RuntimeException('Delivery failed.'));

    expect($completed->refresh()->reminder_sent_at)->toBeNull()
        ->and($alreadySent->refresh()->reminder_sent_at)->not->toBeNull()
        ->and($locked->refresh()->reminder_sent_at)->toBeNull()
        ->and($unconfigured->refresh()->reminder_sent_at)->toBeNull()
        ->and($unconfiguredJob->tags())->toContain('follow-up-task:'.$unconfigured->uuid);
    Log::shouldHaveReceived('warning')->once();
    Log::shouldHaveReceived('error')->once();
});

test('queue failures create safe operational audit records', function (): void {
    Notification::fake();
    config(['services.horizon.notification_email' => 'ops@example.com']);

    $job = Mockery::mock(Job::class);
    $job->allows('resolveName')->andReturn('App\\Jobs\\ExampleJob');
    $job->allows('getQueue')->andReturn('crm');
    $job->allows('uuid')->andReturn('failed-job-uuid');
    $job->allows('getJobId')->andReturn('failed-job-id');

    (new SendFailedJobAlert)->handle(new JobFailed(
        'database',
        $job,
        new RuntimeException('secret exception details'),
    ));

    $activity = Activity::query()->where('description', 'Queue job failed.')->sole();

    expect($activity->causer_id)->toBeNull()
        ->and($activity->properties->toArray())->toMatchArray([
            'connection' => 'database',
            'queue' => 'crm',
            'job' => 'ExampleJob',
            'job_id' => 'failed-job-uuid',
            'exception' => RuntimeException::class,
        ])
        ->and($activity->properties->toArray())->not->toHaveKey('exception_message');
});

test('queue failure alerts avoid notification loops and tolerate a missing recipient', function (): void {
    Notification::fake();

    foreach ([
        [SendQueuedNotifications::class, 'ops@example.com'],
        ['App\\Jobs\\ExampleJob', null],
    ] as [$jobName, $recipient]) {
        config(['services.horizon.notification_email' => $recipient]);
        $job = Mockery::mock(Job::class);
        $job->allows('resolveName')->andReturn($jobName);
        $job->allows('getQueue')->andReturn('mail');
        $job->allows('uuid')->andReturn(null);
        $job->allows('getJobId')->andReturn('failed-job-id');

        (new SendFailedJobAlert)->handle(new JobFailed(
            'database',
            $job,
            new RuntimeException('delivery failed'),
        ));
    }

    Notification::assertNothingSent();
    expect(Activity::query()->where('description', 'Queue job failed.')->count())->toBe(2);
});
