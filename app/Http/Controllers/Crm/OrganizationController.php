<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Actions\CreateOrganization;
use App\Actions\UpdateOrganization;
use App\Data\OrganizationData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\StoreOrganizationRequest;
use App\Http\Requests\Crm\UpdateOrganizationRequest;
use App\Http\Resources\OrganizationResource;
use App\Models\Organization;
use App\Models\User;
use App\Queries\OrganizationQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrganizationController extends Controller
{
    public function index(Request $request, OrganizationQuery $query): Response
    {
        $user = $this->authenticatedUser($request);
        $filters = $request->only(['search', 'owner', 'sort', 'direction', 'archived']);

        return Inertia::render('Crm/Organizations/Index', [
            'organizations' => OrganizationResource::collection($query->build($user, $filters)->paginate(15)->withQueryString()),
            'filters' => $filters,
            'owners' => $this->owners(),
            'canManage' => $user->can('crm.manage'),
            'canAssign' => $user->isCrmAdministrator(),
        ]);
    }

    /** @return array<int, array{id: int, name: string}> */
    private function owners(): array
    {
        return User::query()->where('is_active', true)->orderBy('name')->get(['id', 'name'])->map(fn (User $user): array => [
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
            'canAssign' => $user->isCrmAdministrator(),
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
        $organization->load([
            'owner',
            'contacts' => function (Relation $query) use ($user): void {
                $query
                    ->when(! $user->isCrmAdministrator(), fn (Builder $query): Builder => $query->where(function (Builder $query) use ($user): void {
                        $query->where('owner_id', $user->id)->orWhereNull('owner_id');
                    }))
                    ->with('owner');
            },
        ])->loadCount('leads');

        return Inertia::render('Crm/Organizations/Show', [
            'organization' => OrganizationResource::make($organization)->resolve($request),
            'canManage' => $user->can('crm.manage'),
            'canAssign' => $user->isCrmAdministrator(),
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
            'canAssign' => $user->isCrmAdministrator(),
        ]);
    }

    public function archive(Request $request, Organization $organization): RedirectResponse
    {
        $user = $this->authenticatedUser($request);
        abort_unless($organization->isVisibleTo($user), 404);
        $this->authorize('archive', $organization);
        $organization->update(['archived_at' => now()]);

        return to_route('crm.organizations.index')->with('success', 'Organization archived.');
    }

    public function update(UpdateOrganizationRequest $request, Organization $organization, UpdateOrganization $action): RedirectResponse
    {
        $organization = $action->handle($organization, OrganizationData::fromValidated($request->validated()), $this->authenticatedUser($request));

        return to_route('crm.organizations.show', $organization)->with('success', 'Organization updated.');
    }

    public function restore(Request $request, Organization $organization): RedirectResponse
    {
        $user = $this->authenticatedUser($request);
        abort_unless($organization->isVisibleTo($user), 404);
        $this->authorize('restore', $organization);
        $organization->update(['archived_at' => null]);

        return to_route('crm.organizations.show', $organization)->with('success', 'Organization restored.');
    }

    public function destroy(Request $request, Organization $organization): RedirectResponse
    {
        $user = $this->authenticatedUser($request);
        abort_unless($organization->isVisibleTo($user), 404);
        $this->authorize('delete', $organization);
        $organization->delete();

        return to_route('crm.organizations.index')->with('success', 'Organization deleted.');
    }
}
