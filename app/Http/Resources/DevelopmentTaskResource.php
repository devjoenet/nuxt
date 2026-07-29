<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\DevelopmentNoteAudience;
use App\Models\DevelopmentTask;
use App\Models\DevelopmentTaskLink;
use App\Models\DevelopmentTaskNote;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/** @mixin DevelopmentTask */
class DevelopmentTaskResource extends JsonResource
{
    /** @return array<string, mixed> */
    #[Override]
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $submitter = $this->relationLoaded('submitter') ? $this->getRelation('submitter') : null;
        $reviewer = $this->relationLoaded('reviewer') ? $this->getRelation('reviewer') : null;
        $assignee = $this->relationLoaded('assignee') ? $this->getRelation('assignee') : null;

        return [
            'id' => $this->uuid,
            'title' => $this->title,
            'request_type' => $this->request_type->value,
            'request_type_label' => $this->request_type->label(),
            'affected_area' => $this->affected_area->value,
            'affected_area_label' => $this->affected_area->label(),
            'description' => $this->description,
            'desired_outcome' => $this->desired_outcome,
            'acceptance_criteria' => $this->acceptance_criteria,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'priority' => $this->priority->value,
            'priority_label' => $this->priority->label(),
            'roadmap_position' => $this->roadmap_position,
            'submitter' => $submitter instanceof User ? $submitter->name : 'Former user',
            'reviewer' => $reviewer instanceof User ? $reviewer->name : null,
            'assignee' => $assignee instanceof User ? $assignee->name : null,
            'assignee_id' => $assignee instanceof User ? $assignee->uuid : null,
            'submitted_at' => $this->submitted_at->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'links' => $this->whenLoaded(
                'links',
                fn (): array => DevelopmentTaskLinkResource::collection($this->links)->resolve($request),
            ),
            'notes' => $this->whenLoaded('notes', function () use ($request, $user): array {
                if (! $user instanceof User) {
                    return [];
                }

                return DevelopmentTaskNoteResource::collection(
                    $this->notes
                        ->filter(fn (DevelopmentTaskNote $note): bool => $user->can('view', $note))
                        ->values(),
                )->resolve($request);
            }),
            'can' => $user instanceof User ? [
                'review' => $user->can('review', $this->resource),
                'manage_roadmap' => $user->can('manageRoadmap', $this->resource),
                'update_progress' => $user->can('updateProgress', $this->resource),
                'attach_link' => $user->can('create', [
                    DevelopmentTaskLink::class,
                    $this->resource,
                ]),
                'add_participant_note' => $user->can('create', [
                    DevelopmentTaskNote::class,
                    $this->resource,
                    DevelopmentNoteAudience::Participants,
                ]),
                'add_internal_note' => $user->can('create', [
                    DevelopmentTaskNote::class,
                    $this->resource,
                    DevelopmentNoteAudience::DevelopmentTeam,
                ]),
            ] : [
                'review' => false,
                'manage_roadmap' => false,
                'update_progress' => false,
                'attach_link' => false,
                'add_participant_note' => false,
                'add_internal_note' => false,
            ],
        ];
    }
}
