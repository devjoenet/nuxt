<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Actions\CreateLead;
use App\Actions\UpdateLead;
use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Http\Resources\LeadResource;
use App\Models\Lead;
use App\Models\User;
use App\Queries\LeadQuery;
use BackedEnum;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeadController extends Controller
{
    public function index(Request $request, LeadQuery $query): Response
    {
        $this->authorize('viewAny', Lead::class);
        $user = $this->authenticatedUser($request);
        $filters = $request->only(['search', 'status', 'source', 'owner', 'sort', 'direction', 'archived']);

        return Inertia::render('Crm/Leads/Index', [
            'leads' => LeadResource::collection($query->build($user, $filters)->paginate(15)->withQueryString()),
            'filters' => $filters,
            'statuses' => $this->options(LeadStatus::cases()),
            'sources' => $this->options(LeadSource::cases()),
            'owners' => User::query()->orderBy('name')->get(['id', 'name']),
            'canManage' => $user->can('crm.manage'),
            'canAssign' => $user->isCrmAdministrator(),
        ]);
    }

    /**
     * @param  array<int, BackedEnum>  $cases
     * @return array<int, array{label: string, value: int|string}>
     */
    private function options(array $cases): array
    {
        return array_map(fn (BackedEnum $case): array => [
            'label' => str((string) $case->value)->replace('_', ' ')->title()->toString(),
            'value' => $case->value,
        ], $cases);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Lead::class);
        $user = $this->authenticatedUser($request);

        return Inertia::render('Crm/Leads/Form', [
            'lead' => null,
            'statuses' => $this->options(LeadStatus::cases()),
            'sources' => $this->options(LeadSource::cases()),
            'owners' => User::query()->orderBy('name')->get(['id', 'name']),
            'canAssign' => $user->isCrmAdministrator(),
        ]);
    }

    public function store(StoreLeadRequest $request, CreateLead $action): RedirectResponse
    {
        $lead = $action->handle($request->validated(), $this->authenticatedUser($request));

        return to_route('crm.leads.show', $lead)->with('success', 'Lead created.');
    }

    public function show(Request $request, Lead $lead): Response
    {
        $user = $this->authenticatedUser($request);
        abort_unless($lead->isVisibleTo($user), 404);
        $lead->load([
            'contact',
            'organization',
            'owner',
            'notes.user',
            'activities' => fn (Relation $query): Relation => $query->latest(),
            'followUpTasks' => fn (Relation $query): Relation => $query->orderBy('due_at'),
        ]);

        return Inertia::render('Crm/Leads/Show', [
            'lead' => LeadResource::make($lead),
            'canManage' => $user->can('crm.manage'),
            'canAssign' => $user->isCrmAdministrator(),
        ]);
    }

    public function edit(Request $request, Lead $lead): Response
    {
        $user = $this->authenticatedUser($request);
        abort_unless($lead->isVisibleTo($user), 404);
        $this->authorize('update', $lead);
        $lead->load(['contact', 'organization', 'owner']);

        return Inertia::render('Crm/Leads/Form', [
            'lead' => LeadResource::make($lead),
            'statuses' => $this->options(LeadStatus::cases()),
            'sources' => $this->options(LeadSource::cases()),
            'owners' => User::query()->orderBy('name')->get(['id', 'name']),
            'canAssign' => $user->isCrmAdministrator(),
        ]);
    }

    public function update(UpdateLeadRequest $request, Lead $lead, UpdateLead $action): RedirectResponse
    {
        $action->handle($lead, $request->validated(), $this->authenticatedUser($request));

        return to_route('crm.leads.show', $lead)->with('success', 'Lead updated.');
    }
}
