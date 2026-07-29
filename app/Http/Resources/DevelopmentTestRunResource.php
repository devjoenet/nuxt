<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\DevelopmentTestRunStatus;
use App\Models\DevelopmentTask;
use App\Models\DevelopmentTestRun;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/** @mixin DevelopmentTestRun */
class DevelopmentTestRunResource extends JsonResource
{
    /** @return array<string, mixed> */
    #[Override]
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $task = $this->relationLoaded('task') ? $this->getRelation('task') : null;
        $tester = $this->relationLoaded('tester') ? $this->getRelation('tester') : null;
        $requestedBy = $this->relationLoaded('requestedBy') ? $this->getRelation('requestedBy') : null;

        return [
            'id' => $this->uuid,
            'instructions' => $this->instructions,
            'build_reference' => $this->build_reference,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'feedback' => $this->feedback,
            'tester' => $tester instanceof User ? $tester->name : 'Former user',
            'requested_by' => $requestedBy instanceof User ? $requestedBy->name : 'Former user',
            'requested_at' => $this->requested_at->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'task' => $task instanceof DevelopmentTask ? [
                'id' => $task->uuid,
                'title' => $task->title,
                'status' => $task->status->value,
                'status_label' => $task->status->label(),
                'affected_area_label' => $task->affected_area->label(),
                'acceptance_criteria' => $task->acceptance_criteria,
            ] : null,
            'can' => [
                'record_result' => $user instanceof User
                    && $this->status === DevelopmentTestRunStatus::Pending
                    && $user->can('update', $this->resource),
            ],
        ];
    }
}
