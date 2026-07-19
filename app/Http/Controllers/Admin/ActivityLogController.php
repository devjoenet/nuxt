<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Queries\ActivityLogQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request, ActivityLogQuery $activityLogQuery): Response
    {
        $this->authorize('viewAny', Activity::class);
        $filters = [
            'search' => $request->string('search')->trim()->value() ?: null,
            'event' => $this->normalizeArrayFilter($request->input('event')),
            'causer' => $this->normalizeArrayFilter($request->input('causer')),
            'subject' => $this->normalizeArrayFilter($request->input('subject')),
            'from' => $request->string('from')->trim()->value() ?: null,
            'to' => $request->string('to')->trim()->value() ?: null,
        ];
        $activities = $activityLogQuery->build($filters)
            ->paginate(25)
            ->withQueryString();
        $options = $activityLogQuery->filterOptions();

        return Inertia::render('Admin/Audit/Index', [
            'activities' => $activities->through(function (mixed $activity): array {
                /** @var Activity $activity */
                $causer = $activity->causer;

                return [
                    'id' => $activity->id,
                    'description' => $activity->description,
                    'event' => $activity->event,
                    'log_name' => $activity->log_name,
                    'causer' => $causer instanceof User ? $causer->name : null,
                    'subject' => $activity->subject ? class_basename($activity->subject) : null,
                    'changes' => $activity->attribute_changes?->toArray(),
                    'created_at' => $activity->created_at?->toISOString(),
                ];
            }),
            'filters' => $filters,
            'eventOptions' => $options['events'],
            'causerOptions' => $options['causers'],
            'subjectOptions' => $options['subjects'],
        ]);
    }

    /** @return array<int, string> */
    private function normalizeArrayFilter(mixed $value): array
    {
        return array_values(array_unique(array_filter(array_map(
            static fn (mixed $item): ?string => is_string($item) && ($item = mb_trim($item)) !== '' ? $item : null,
            Arr::wrap($value),
        ))));
    }
}
