<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\User;
use App\Queries\LeadHistoryQuery;
use App\Support\AuditMorphs;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class LeadHistoryController extends Controller
{
    public function __invoke(Request $request, Lead $lead, LeadHistoryQuery $query): Response
    {
        abort_unless($lead->isVisibleTo($this->authenticatedUser($request)), 404);
        $this->authorize('viewAny', Activity::class);

        return Inertia::render('Crm/Leads/History', [
            'lead' => ['id' => $lead->uuid],
            'activities' => $query->build($lead)->paginate(15)->through(function (Activity $activity): array {
                $causer = $activity->causer;

                return [
                    'id' => $activity->getKey(),
                    'action' => Str::headline($activity->event ?? $activity->description),
                    'subject' => AuditMorphs::label($activity->subject_type),
                    'actor' => $causer instanceof User ? $causer->name : ($activity->causer_id === null ? 'System' : 'Unavailable actor'),
                    'changes' => $activity->attribute_changes?->toArray() ?? [],
                    'created_at' => $activity->created_at?->toISOString(),
                ];
            }),
        ]);
    }
}
