<?php

declare(strict_types=1);

use App\Actions\ArchiveContact;
use App\Actions\ArchiveLead;
use App\Actions\ArchiveOrganization;
use App\Actions\CompleteFollowUpTask;
use App\Actions\ConvertLead;
use App\Actions\CreateFollowUpTask;
use App\Actions\CreateLeadNote;
use App\Actions\DeleteContact;
use App\Actions\DeleteOrganization;
use App\Actions\RestoreContact;
use App\Actions\RestoreLead;
use App\Actions\RestoreOrganization;
use App\Data\FollowUpTaskData;
use App\Data\LeadNoteData;
use App\Enums\TaskPriority;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;
use SoutheastCode\Crm\Contacts\Models\Contact;
use SoutheastCode\Crm\FollowUps\Enums\FollowUpTaskStatus;
use SoutheastCode\Crm\FollowUps\Models\FollowUpTask;
use SoutheastCode\Crm\Leads\Models\Lead;
use SoutheastCode\Crm\Organizations\Models\Organization;
use SoutheastCode\Crm\Shared\Enums\CrmActivityType;
use SoutheastCode\Crm\Shared\Models\CrmActivity;
use Spatie\Activitylog\Models\Activity;

test('completing a follow-up is atomic, idempotent, and attributed to its actor', function (): void {
    $actor = User::factory()->create();
    $lead = Lead::factory()->create();
    $task = FollowUpTask::factory()->create(['lead_id' => $lead->id]);
    $action = app(CompleteFollowUpTask::class);

    $completed = $action->handle($lead, $task, $actor);
    $action->handle($lead, $task, $actor);

    expect($completed->status)->toBe(FollowUpTaskStatus::Completed)
        ->and($completed->completed_at)->not->toBeNull()
        ->and(CrmActivity::query()->where('lead_id', $lead->id)->where('type', CrmActivityType::TaskCompleted)->count())->toBe(1)
        ->and(CrmActivity::query()->where('lead_id', $lead->id)->where('type', CrmActivityType::TaskCompleted)->sole()->user_id)->toBe($actor->id)
        ->and(Activity::query()->where('subject_type', FollowUpTask::class)->where('subject_id', $task->id)->where('causer_id', $actor->id)->exists())->toBeTrue();
});

test('a follow-up cannot be completed against a different lead', function (): void {
    $actor = User::factory()->create();
    $task = FollowUpTask::factory()->create();
    $otherLead = Lead::factory()->create();

    expect(fn (): FollowUpTask => app(CompleteFollowUpTask::class)->handle($otherLead, $task, $actor))
        ->toThrow(ModelNotFoundException::class);

    expect($task->refresh()->status)->toBe(FollowUpTaskStatus::Open)
        ->and(CrmActivity::query()->where('lead_id', $otherLead->id)->count())->toBe(0);
});

test('lead note and follow-up actions persist paired CRM records with their explicit actor', function (): void {
    $actor = User::factory()->create();
    $owner = User::factory()->create();
    $lead = Lead::factory()->create(['owner_id' => $owner->id]);

    $note = app(CreateLeadNote::class)->handle($lead, new LeadNoteData('Keep the implementation notes internal.'), $actor);
    $task = app(CreateFollowUpTask::class)->handle($lead, new FollowUpTaskData(
        title: 'Schedule implementation review',
        description: null,
        priority: TaskPriority::High,
        dueAt: Carbon::now()->addDay(),
    ), $actor);

    expect($note->user_id)->toBe($actor->id)
        ->and($task->assigned_to)->toBe($owner->id)
        ->and(CrmActivity::query()->where('lead_id', $lead->id)->where('type', CrmActivityType::NoteAdded)->sole()->user_id)->toBe($actor->id)
        ->and(Activity::query()->where('subject_type', FollowUpTask::class)->where('subject_id', $task->id)->where('causer_id', $actor->id)->exists())->toBeTrue();
});

test('CRM lifecycle actions return refreshed models and preserve direct-call audit attribution', function (): void {
    $actor = User::factory()->create();
    $lead = Lead::factory()->create();
    $organization = Organization::factory()->create();
    $contact = Contact::factory()->create();

    $archivedLead = app(ArchiveLead::class)->handle($lead, $actor);
    expect($archivedLead->archived_at)->not->toBeNull();

    $restoredLead = app(RestoreLead::class)->handle($lead, $actor);
    expect($restoredLead->archived_at)->toBeNull();

    $archivedOrganization = app(ArchiveOrganization::class)->handle($organization, $actor);
    expect($archivedOrganization->archived_at)->not->toBeNull();

    $restoredOrganization = app(RestoreOrganization::class)->handle($organization, $actor);
    expect($restoredOrganization->archived_at)->toBeNull();

    app(DeleteOrganization::class)->handle($organization, $actor);
    $archivedContact = app(ArchiveContact::class)->handle($contact, $actor);
    expect($archivedContact->archived_at)->not->toBeNull();

    $restoredContact = app(RestoreContact::class)->handle($contact, $actor);
    expect($restoredContact->archived_at)->toBeNull();

    app(DeleteContact::class)->handle($contact, $actor);

    expect(Organization::query()->find($organization->id))->toBeNull()
        ->and(Contact::query()->find($contact->id))->toBeNull()
        ->and(Activity::query()->where('causer_id', $actor->id)->count())->toBeGreaterThan(0);
});

test('lead conversion accepts an explicit actor outside HTTP', function (): void {
    $actor = User::factory()->create();
    $organization = Organization::factory()->create();
    $lead = Lead::factory()->create(['organization_id' => $organization->id]);

    $converted = app(ConvertLead::class)->handle($lead, $actor);

    expect($converted->converted_at)->not->toBeNull()
        ->and(CrmActivity::query()->where('lead_id', $lead->id)->where('type', CrmActivityType::LeadConverted)->sole()->user_id)->toBe($actor->id);
});
