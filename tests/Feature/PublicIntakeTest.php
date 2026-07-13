<?php

declare(strict_types=1);

use App\Enums\CrmActivityType;
use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Models\Contact;
use App\Models\CrmActivity;
use App\Models\FollowUpTask;
use App\Models\Lead;
use App\Models\Organization;
use App\Notifications\NewLeadReceived;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Notification;

afterEach(function (): void {
    Date::setTestNow();
});

function validIntake(array $overrides = []): array
{
    return [
        'name' => 'Jordan Rivera',
        'email' => 'jordan@example.com',
        'company' => 'Rivera Logistics',
        'phone' => '+1 (912) 555-0142',
        'website' => 'https://rivera-logistics.example',
        'budget_range' => '25k-50k',
        'timeline' => '1-3-months',
        'service_interest' => 'operations-app',
        'referral_source' => 'Referral',
        'project_details' => 'Our dispatch workflow depends on several spreadsheets and repeated manual entry.',
        'consent' => '1',
        'landing_page' => '/',
        'utm_source' => 'newsletter',
        ...$overrides,
    ];
}

test('a guest can submit a public inquiry and create the complete lead chain', function (): void {
    Date::setTestNow('2026-07-10 10:00:00');
    Notification::fake();
    config(['services.leads.recipient' => 'leads@southeastcode.com']);

    $response = $this->post(route('contact.store'), validIntake());

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('home'));

    expect(Organization::query()->count())->toBe(1)
        ->and(Contact::query()->count())->toBe(1)
        ->and(Lead::query()->count())->toBe(1)
        ->and(CrmActivity::query()->count())->toBe(1)
        ->and(FollowUpTask::query()->count())->toBe(1);

    $lead = Lead::query()->with(['contact', 'organization', 'activities', 'followUpTasks'])->sole();

    expect($lead->contact->email)->toBe('jordan@example.com')
        ->and($lead->organization?->name)->toBe('Rivera Logistics')
        ->and($lead->status)->toBe(LeadStatus::New)
        ->and($lead->source)->toBe(LeadSource::Website)
        ->and($lead->activities->sole()->type)->toBe(CrmActivityType::PublicIntakeCreated)
        ->and($lead->followUpTasks->sole()->due_at->toDateTimeString())->toBe('2026-07-13 17:00:00');

    Notification::assertSentOnDemand(
        NewLeadReceived::class,
        fn (NewLeadReceived $notification, array $channels, object $notifiable): bool => $notifiable->routes['mail'] === 'leads@southeastcode.com'
            && $notification->lead->is($lead)
            && $channels === ['mail'],
    );
});

test('organization is optional for public inquiries', function (): void {
    Notification::fake();

    $this->post(route('contact.store'), validIntake([
        'company' => null,
        'website' => null,
    ]))->assertSessionHasNoErrors();

    expect(Organization::query()->count())->toBe(0)
        ->and(Lead::query()->sole()->organization_id)->toBeNull();
});

test('repeat inquiries update the contact without duplicating it', function (): void {
    Notification::fake();

    $this->post(route('contact.store'), validIntake());
    $this->post(route('contact.store'), validIntake(['name' => 'Jordan R.']));

    expect(Contact::query()->count())->toBe(1)
        ->and(Contact::query()->sole()->name)->toBe('Jordan R.')
        ->and(Lead::query()->count())->toBe(2);
});

test('public inquiry validation rejects invalid or spam submissions', function (array $overrides, array $errors): void {
    Notification::fake();

    $this->from(route('home'))
        ->post(route('contact.store'), validIntake($overrides))
        ->assertSessionHasErrors($errors)
        ->assertRedirect(route('home'));

    expect(Lead::query()->count())->toBe(0);
})->with([
    'required fields' => [['name' => '', 'email' => '', 'project_details' => '', 'consent' => null], ['name', 'email', 'project_details', 'consent']],
    'invalid email' => [['email' => 'not-an-email'], ['email']],
    'invalid website' => [['website' => 'ftp://example.com'], ['website']],
    'honeypot' => [['website_confirmation' => 'bot-filled-this'], ['website_confirmation']],
]);

test('public inquiries are rate limited by IP address', function (): void {
    Notification::fake();

    foreach (range(1, 5) as $attempt) {
        $this->post(route('contact.store'), validIntake(['email' => "lead{$attempt}@example.com"]))
            ->assertRedirect(route('home'));
    }

    $this->post(route('contact.store'), validIntake(['email' => 'blocked@example.com']))
        ->assertTooManyRequests();
});
