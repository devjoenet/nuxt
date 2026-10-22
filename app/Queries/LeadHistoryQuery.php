<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\CrmActivity;
use App\Models\FollowUpTask;
use App\Models\Lead;
use App\Models\Note;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use SoutheastCode\Audit\Support\AuditMorphs;
use Spatie\Activitylog\Models\Activity;

final class LeadHistoryQuery
{
    /** @return Builder<Activity> */
    public function build(Lead $lead): Builder
    {
        $noteType = (new Note)->getMorphClass();
        $taskType = (new FollowUpTask)->getMorphClass();
        $activityType = (new CrmActivity)->getMorphClass();
        $leadType = $lead->getMorphClass();

        $noteAudits = Activity::query()->select('subject_id')
            ->where('subject_type', $noteType)
            ->where(fn (Builder $query): Builder => $query
                ->where(fn (Builder $query): Builder => $query
                    ->where('attribute_changes->attributes->subject_type', $leadType)
                    ->where('attribute_changes->attributes->subject_id', $lead->getKey()))
                ->orWhere(fn (Builder $query): Builder => $query
                    ->where('attribute_changes->old->subject_type', $leadType)
                    ->where('attribute_changes->old->subject_id', $lead->getKey())));
        $taskAudits = Activity::query()->select('subject_id')
            ->where('subject_type', $taskType)
            ->where(fn (Builder $query): Builder => $query
                ->where('attribute_changes->attributes->lead_id', $lead->getKey())
                ->orWhere('attribute_changes->old->lead_id', $lead->getKey()));

        return Activity::query()->afterQuery(fn (mixed $activities): mixed => $activities instanceof EloquentCollection ? AuditMorphs::load($activities, ['causer']) : $activities)
            ->where(function (Builder $query) use ($lead, $leadType, $noteType, $taskType, $noteAudits, $taskAudits, $activityType): void {
                $query->where(fn (Builder $query): Builder => $query
                    ->where('subject_type', $leadType)->where('subject_id', $lead->getKey()))
                    ->orWhere(fn (Builder $query): Builder => $query
                        ->where('subject_type', $noteType)
                        ->where(fn (Builder $query): Builder => $query
                            ->whereIn('subject_id', Note::withTrashed()->select('id')->where('subject_type', $leadType)->where('subject_id', $lead->getKey()))
                            ->orWhereIn('subject_id', $noteAudits)))
                    ->orWhere(fn (Builder $query): Builder => $query
                        ->where('subject_type', $activityType)
                        ->whereIn('subject_id', CrmActivity::query()->select('id')->where('lead_id', $lead->getKey())))
                    ->orWhere(fn (Builder $query): Builder => $query
                        ->where('subject_type', $taskType)
                        ->where(fn (Builder $query): Builder => $query
                            ->whereIn('subject_id', FollowUpTask::withTrashed()->select('id')->where('lead_id', $lead->getKey()))
                            ->orWhereIn('subject_id', $taskAudits)));
            })
            ->orderByDesc('created_at')->orderByDesc('id');
    }
}
