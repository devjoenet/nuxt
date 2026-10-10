<?php

declare(strict_types=1);

namespace App\Queries;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;

final class RecordHistoryQuery
{
    /** @return Builder<Activity> */
    public function build(Model $record): Builder
    {
        return Activity::query()->with('causer')
            ->where('subject_type', $record->getMorphClass())
            ->where('subject_id', $record->getKey())
            ->orderByDesc('created_at')->orderByDesc('id');
    }
}
