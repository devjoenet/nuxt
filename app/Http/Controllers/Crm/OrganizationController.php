<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Actions\ArchiveOrganization;
use App\Actions\CreateOrganization;
use App\Actions\DeleteOrganization;
use App\Actions\RestoreOrganization;
use App\Actions\UpdateOrganization;
use App\Data\OrganizationData;
use App\Data\OrganizationIndexData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\OrganizationIndexRequest;
use App\Http\Requests\Crm\StoreOrganizationRequest;
use App\Http\Requests\Crm\UpdateOrganizationRequest;
use App\Http\Resources\OrganizationResource;
use App\Models\Contact;
use App\Models\Lead;
use App\Models\Organization;
use App\Models\User;
use App\Queries\OrganizationQuery;
use App\Support\CrmAccess;
use App\Support\WorkspaceUsers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class OrganizationController extends Controller
{
    public function index(OrganizationIndexRequest $request, OrganizationQuery $query): Response
    {
        $user = $this->authenticatedUser($request);
        $filters = OrganizationIndexData::fromValidated($request->validated());

        return Inertia::render('Crm/Organizations/Index', [
            'organizations' => OrganizationResource::collection($query->build($user, $filters)->paginate(15)->withQueryString()),
            'filters' => $filters->filters(),
            'owners' => $this->owners(),
            'canManage' => $user->can('crm.manage'),
            'canAssign' => CrmAccess::administrator($user),
        ]);
    }

    /** @return array<int, array{id: int, name: string}> */
    private function owners(): array
    {
        return WorkspaceUsers::model()::query()->where('is_active', true)->orderBy('name')->get(['id', 'name'])->map(fn (User $user): array => [
            'id' => $user->id,
            'name' => $user->name,
        ])->all();
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Organization::class);
        $user = $this->authenticatedUser($request);

        return Inertia::render('Crm/Organizations/Form', [
            'organization' => null,
            'owners' => $this->owners(),
            'canAssign' => CrmAccess::administrator($user),
        ]);
    }

    public function store(StoreOrganizationRequest $request, CreateOrganization $action): RedirectResponse
    {
        $organization = $action->handle(OrganizationData::fromValidated($request->validated()), $this->authenticatedUser($request));

        return to_route('crm.organizations.show', $organization)->with('success', 'Organization created.');
    }

    public function show(Request $request, Organization $organization): Response
    {
        $user = $this->authenticatedUser($request);
        abort_unless($organization->isVisibleTo($user), 404);
        $organization->load('owner')->setRelation(
            'contacts',
            $organization->contacts()->visibleTo($user)->with('owner')->get(),
        )->loadCount([
            'contacts' => function (Builder $query) use ($user): void {
                Contact::constrainVisibility($query, $user);
            },
            'leads' => function (Builder $query) use ($user): void {
                Lead::constrainVisibility($query, $user);
            },
        ]);

        return Inertia::render('Crm/Organizations/Show', [
            'organization' => OrganizationResource::make($organization)->resolve($request),
            'canManage' => $user->can('crm.manage'),
            'canViewHistory' => $user->can('viewAny', Activity::class),
            'canAssign' => CrmAccess::administrator($user),
        ]);
    }

    public function edit(Request $request, Organization $organization): Response
    {
        $user = $this->authenticatedUser($request);
        abort_unless($organization->isVisibleTo($user), 404);
        $this->authorize('update', $organization);

        return Inertia::render('Crm/Organizations/Form', [
            'organization' => OrganizationResource::make($organization->load('owner'))->resolve($request),
            'owners' => $this->owners(),
            'canAssign' => CrmAccess::administrator($user),
        ]);
    }

    public function archive(Request $request, Organization $organization, ArchiveOrganization $action): RedirectResponse
    {
        $user = $this->authenticatedUser($request);
        abort_unless($organization->isVisibleTo($user), 404);
        $this->authorize('archive', $organization);
        $action->handle($organization, $user);

        return to_route('crm.organizations.index')->with('success', 'Organization archived.');
    }

    public function update(UpdateOrganizationRequest $request, Organization $organization, UpdateOrganization $action): RedirectResponse
    {
        $organization = $action->handle($organization, OrganizationData::fromValidated($request->validated()), $this->authenticatedUser($request));

        return to_route('crm.organizations.show', $organization)->with('success', 'Organization updated.');
    }

    public function restore(Request $request, Organization $organization, RestoreOrganization $action): RedirectResponse
    {
        $user = $this->authenticatedUser($request);
        abort_unless($organization->isVisibleTo($user), 404);
        $this->authorize('restore', $organization);
        $action->handle($organization, $user);

        return to_route('crm.organizations.show', $organization)->with('success', 'Organization restored.');
    }

    public function destroy(Request $request, Organization $organization, DeleteOrganization $action): RedirectResponse
    {
        $user = $this->authenticatedUser($request);
        abort_unless($organization->isVisibleTo($user), 404);
        $this->authorize('delete', $organization);
        $action->handle($organization, $user);

        return to_route('crm.organizations.index')->with('success', 'Organization deleted.');
    }
}
