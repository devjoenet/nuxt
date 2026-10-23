<?php

declare(strict_types=1);

use App\Actions\SubmitPublicIntake;
use App\Data\IntakeData;
use App\Models\BusinessSetting;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use SoutheastCode\Crm\Contacts\Models\Contact;
use SoutheastCode\Crm\Leads\Models\Lead;

test('host intake notification waits for the outer commit and uses the agency recipient', function (): void {
    Mail::fake();
    BusinessSetting::current()->update(['notification_email' => 'agency@example.com']);
    $deliveries = [];
    Event::listen(NotificationSent::class, function (NotificationSent $event) use (&$deliveries): void {
        $deliveries[] = $event->notifiable->routes['mail'];
    });

    DB::transaction(function () use (&$deliveries): void {
        app(SubmitPublicIntake::class)->handle(new IntakeData(
            name: 'Avery', email: 'avery@example.com', projectDetails: 'A validated inquiry', consent: '1',
        ));
        expect($deliveries)->toBe([]);
        expect(Lead::query()->count())->toBe(1);
    });

    expect($deliveries)->toBe(['agency@example.com']);
});

test('rolling back host intake discards the complete lead chain and notification', function (): void {
    Mail::fake();
    $deliveries = [];
    Event::listen(NotificationSent::class, function (NotificationSent $event) use (&$deliveries): void {
        $deliveries[] = $event->channel;
    });

    expect(function (): void {
        DB::transaction(function (): void {
            app(SubmitPublicIntake::class)->handle(new IntakeData(
                name: 'Avery', email: 'avery@example.com', projectDetails: 'A validated inquiry', consent: '1',
            ));
            throw new RuntimeException('Rollback outer transaction');
        });
    })->toThrow(RuntimeException::class, 'Rollback outer transaction');

    expect($deliveries)->toBe([]);
    expect(Lead::query()->count())->toBe(0);
    expect(Contact::query()->count())->toBe(0);
});
