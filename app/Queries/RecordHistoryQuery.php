<?php

declare(strict_types=1);

namespace App\Queries;

use App\Support\AuditMorphs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;

final class RecordHistoryQuery
{
    /** @return Builder<Activity> */
    public function build(Model $record): Builder
    {
        return Activity::query()->afterQuery(fn (mixed $activities): mixed => $activities instanceof EloquentCollection ? AuditMorphs::load($activities, ['causer']) : $activities)
            ->where('subject_type', $record->getMorphClass())
            ->where('subject_id', $record->getKey())
            ->orderByDesc('created_at')->orderByDesc('id');
    }
}
