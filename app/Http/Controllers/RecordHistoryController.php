<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\DevelopmentTask;
use App\Models\Organization;
use App\Models\User;
use App\Queries\RecordHistoryQuery;
use App\Support\AuditMorphs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class RecordHistoryController extends Controller
{
    public function contact(Request $request, Contact $contact, RecordHistoryQuery $query): Response
    {
        abort_unless($contact->isVisibleTo($this->authenticatedUser($request)), 404);

        return $this->render($contact, $query, 'Contact', 'crm.contacts');
    }

    public function organization(Request $request, Organization $organization, RecordHistoryQuery $query): Response
    {
        abort_unless($organization->isVisibleTo($this->authenticatedUser($request)), 404);

        return $this->render($organization, $query, 'Organization', 'crm.organizations');
    }

    public function developmentTask(DevelopmentTask $developmentTask, RecordHistoryQuery $query): Response
    {
        $this->authorize('view', $developmentTask);

        return $this->render($developmentTask, $query, 'Development task', 'development.tasks');
    }

    private function render(Model $record, RecordHistoryQuery $query, string $label, string $routePrefix): Response
    {
        $this->authorize('viewAny', Activity::class);

        return Inertia::render('History/Show', [
            'label' => $label,
            'backUrl' => route("{$routePrefix}.show", $record),
            'historyUrl' => route("{$routePrefix}.history", $record),
            'activities' => $query->build($record)->paginate(15)->through(function (Activity $activity): array {
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
