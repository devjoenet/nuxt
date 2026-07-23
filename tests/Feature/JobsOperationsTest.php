<?php

declare(strict_types=1);

use App\Enums\RoleName;
use App\Jobs\SendFollowUpTaskReminder;
use App\Listeners\SendFailedJobAlert;
use App\Models\BusinessSetting;
use App\Models\FollowUpTask;
use App\Models\User;
use App\Notifications\FollowUpTaskReminder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Contracts\Mail\Factory as MailFactory;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Mail\Markdown;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia as Assert;
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
