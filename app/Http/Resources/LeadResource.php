<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\CrmActivity;
use App\Models\FollowUpTask;
use App\Models\Lead;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/** @mixin Lead */
class LeadResource extends JsonResource
{
    /** @return array<string, mixed> */
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'status' => $this->status->value,
            'source' => $this->source->value,
            'service_interest' => $this->service_interest,
            'service_interests' => $this->service_interests ?? ($this->service_interest ? [$this->service_interest] : []),
            'budget_range' => $this->budget_range,
            'timeline' => $this->timeline,
            'project_details' => $this->project_details,
            'archived_at' => $this->archived_at?->toISOString(),
            'converted_at' => $this->converted_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'contact' => [
                'id' => $this->contact->uuid,
                'name' => $this->contact->name,
                'email' => $this->contact->email,
                'phone' => $this->contact->phone,
            ],
            'organization' => $this->organization ? [
                'id' => $this->organization->uuid,
                'name' => $this->organization->name,
                'website' => $this->organization->website,
                'client_since_at' => $this->organization->client_since_at?->toISOString(),
            ] : null,
            'owner' => $this->owner ? ['id' => $this->owner->id, 'name' => $this->owner->name] : null,
            'notes' => $this->whenLoaded('notes', fn (): array => $this->notes->map(fn (Note $note): array => [
                'id' => $note->uuid,
                'body' => $note->body,
                'author' => $note->user->name,
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
