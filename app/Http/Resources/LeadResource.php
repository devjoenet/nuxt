<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;
use SoutheastCode\Crm\Contacts\Models\Contact;
use SoutheastCode\Crm\FollowUps\Models\FollowUpTask;
use SoutheastCode\Crm\Leads\Models\Lead;
use SoutheastCode\Crm\Organizations\Models\Organization;
use SoutheastCode\Crm\Shared\Models\CrmActivity;
use SoutheastCode\Crm\Shared\Models\Note;

/** @mixin Lead */
class LeadResource extends JsonResource
{
    /** @return array<string, mixed> */
    #[Override]
    public function toArray(Request $request): array
    {
        $contact = $this->relationLoaded('contact') ? $this->getRelation('contact') : null;
        $organization = $this->relationLoaded('organization') ? $this->getRelation('organization') : null;
        $user = $request->user();

        return [
            'id' => $this->uuid,
            'status' => $this->status->value,
            'source' => $this->source->value,
            'service_interest' => $this->service_interest,
            'service_interests' => $this->service_interests ?? ($this->service_interest ? [$this->service_interest] : []),
            'budget_range' => $this->budget_range,
            'timeline' => $this->timeline,
            'project_details' => $this->project_details,
            'intake_snapshot' => $this->intake_snapshot,
            'archived_at' => $this->archived_at?->toISOString(),
            'converted_at' => $this->converted_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'contact' => $contact instanceof Contact && $user instanceof User && $contact->isVisibleTo($user) ? [
                'id' => $contact->uuid,
                'name' => $contact->name,
                'email' => $contact->email,
                'phone' => $contact->phone,
                'deleted_at' => $contact->deleted_at?->toISOString(),
            ] : null,
            'organization' => $organization instanceof Organization && $user instanceof User && $organization->isVisibleTo($user) ? [
                'id' => $organization->uuid,
                'name' => $organization->name,
                'website' => $organization->website,
                'client_since_at' => $organization->client_since_at?->toISOString(),
            ] : null,
            'owner' => $this->owner ? ['id' => $this->owner->workspaceId(), 'name' => $this->owner->workspaceName()] : null,
            'notes' => $this->whenLoaded('notes', fn (): array => $this->notes->map(fn (Note $note): array => [
                'id' => $note->uuid,
                'body' => $note->body,
                'author' => $note->user === null ? 'Unknown user' : $note->user->workspaceName(),
                'created_at' => $note->created_at->toISOString(),
            ])->all()),
            'activities' => $this->whenLoaded('activities', fn (): array => $this->activities->map(fn (CrmActivity $activity): array => [
                'id' => $activity->uuid,
                'type' => $activity->type->value,
                'description' => $activity->description,
                'created_at' => $activity->created_at->toISOString(),
            ])->all()),
            'tasks' => $this->whenLoaded('followUpTasks', fn (): array => $this->followUpTasks->map(fn (FollowUpTask $task): array => [
                'id' => $task->uuid,
                'title' => $task->title,
                'status' => $task->status->value,
                'priority' => $task->priority->value,
                'due_at' => $task->due_at->toISOString(),
            ])->all()),
        ];
    }
}
