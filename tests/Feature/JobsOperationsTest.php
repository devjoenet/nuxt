<?php

declare(strict_types=1);

use App\Enums\RoleName;
use App\Jobs\SendFollowUpTaskReminder;
use App\Models\BusinessSetting;
use App\Models\FollowUpTask;
use App\Models\User;
use App\Notifications\FollowUpTaskReminder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia as Assert;

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

test('the reminder command dispatches due tasks onto the reminders queue', function (): void {
    Queue::fake();
    $task = FollowUpTask::factory()->create(['due_at' => now()->subHour()]);

    $this->artisan('crm:dispatch-follow-up-reminders')->assertSuccessful();

    Queue::assertPushed(SendFollowUpTaskReminder::class, fn (SendFollowUpTaskReminder $job): bool => $job->taskUuid === $task->uuid
        && $job->queue === 'reminders');
});

test('a follow-up reminder is sent once per day', function (): void {
    Notification::fake();
    $task = FollowUpTask::factory()->create(['due_at' => now()->subHour()]);
    BusinessSetting::current()->update(['notification_email' => 'ops@example.com']);
    $job = new SendFollowUpTaskReminder($task->uuid, now()->toDateString());

    $job->handle();
    $job->handle();

    Notification::assertSentOnDemand(
        FollowUpTaskReminder::class,
        fn (FollowUpTaskReminder $notification, array $channels, object $notifiable): bool => $notifiable->routes['mail'] === 'ops@example.com'
            && $notification->task->is($task)
            && $channels === ['mail'],
    );
    Notification::assertSentOnDemandTimes(FollowUpTaskReminder::class, 1);
    expect($task->refresh()->reminder_sent_at)->not->toBeNull();
});
