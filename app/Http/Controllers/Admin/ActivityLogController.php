<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Data\ActivityLogFiltersData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AuditIndexRequest;
use App\Models\User;
use App\Queries\ActivityLogQuery;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    public function index(AuditIndexRequest $request, ActivityLogQuery $activityLogQuery): Response
    {
        $filters = ActivityLogFiltersData::fromValidated($request->validated());
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
            'filters' => $filters->filters(),
            'eventOptions' => $options['events'],
            'causerOptions' => $options['causers'],
            'subjectOptions' => $options['subjects'],
        ]);
    }
}
