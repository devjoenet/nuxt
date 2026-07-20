<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Actions\CreateContact;
use App\Actions\UpdateContact;
use App\Data\ContactData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\StoreContactRequest;
use App\Http\Requests\Crm\UpdateContactRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use App\Models\Organization;
use App\Models\User;
use App\Queries\ContactQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function index(Request $request, ContactQuery $query): Response
    {
        $user = $this->authenticatedUser($request);
        $filters = $request->only(['search', 'organization', 'owner', 'sort', 'direction', 'archived']);

        return Inertia::render('Crm/Contacts/Index', [
            'contacts' => ContactResource::collection($query->build($user, $filters)->paginate(15)->withQueryString()),
            'filters' => $filters,
            'organizations' => Organization::query()->visibleTo($user)->whereNull('archived_at')->orderBy('name')->get(['id', 'name']),
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
        $this->authorize('create', Contact::class);
        $user = $this->authenticatedUser($request);

        return Inertia::render('Crm/Contacts/Form', [
            'contact' => null,
            'organizations' => Organization::query()->visibleTo($user)->whereNull('archived_at')->orderBy('name')->get(['id', 'name']),
            'owners' => $this->owners(),
            'canAssign' => $user->isCrmAdministrator(),
        ]);
    }

    public function store(StoreContactRequest $request, CreateContact $action): RedirectResponse
    {
        $contact = $action->handle(ContactData::fromValidated($request->validated()), $this->authenticatedUser($request));

        return to_route('crm.contacts.show', $contact)->with('success', 'Contact created.');
    }

    public function show(Request $request, Contact $contact): Response
    {
        $user = $this->authenticatedUser($request);
        abort_unless($contact->isVisibleTo($user), 404);
        $contact->load(['organization', 'owner'])->loadCount('leads');

        return Inertia::render('Crm/Contacts/Show', [
            'contact' => ContactResource::make($contact)->resolve($request),
            'canManage' => $user->can('crm.manage'),
        ]);
    }

    public function edit(Request $request, Contact $contact): Response
    {
        $user = $this->authenticatedUser($request);
        abort_unless($contact->isVisibleTo($user), 404);
        $this->authorize('update', $contact);

        return Inertia::render('Crm/Contacts/Form', [
            'contact' => ContactResource::make($contact->load(['organization', 'owner']))->resolve($request),
            'organizations' => Organization::query()->visibleTo($user)->whereNull('archived_at')->orderBy('name')->get(['id', 'name']),
            'owners' => $this->owners(),
            'canAssign' => $user->isCrmAdministrator(),
        ]);
    }

    public function archive(Request $request, Contact $contact): RedirectResponse
    {
        $user = $this->authenticatedUser($request);
        abort_unless($contact->isVisibleTo($user), 404);
        $this->authorize('archive', $contact);
        $contact->update(['archived_at' => now()]);

        return to_route('crm.contacts.index')->with('success', 'Contact archived.');
    }

    public function update(UpdateContactRequest $request, Contact $contact, UpdateContact $action): RedirectResponse
    {
        $contact = $action->handle($contact, ContactData::fromValidated($request->validated()), $this->authenticatedUser($request));

        return to_route('crm.contacts.show', $contact)->with('success', 'Contact updated.');
    }

    public function restore(Request $request, Contact $contact): RedirectResponse
    {
        $user = $this->authenticatedUser($request);
        abort_unless($contact->isVisibleTo($user), 404);
        $this->authorize('restore', $contact);
        $contact->update(['archived_at' => null]);

        return to_route('crm.contacts.show', $contact)->with('success', 'Contact restored.');
    }

    public function destroy(Request $request, Contact $contact): RedirectResponse
    {
        $user = $this->authenticatedUser($request);
        abort_unless($contact->isVisibleTo($user), 404);
        $this->authorize('delete', $contact);
        $contact->delete();

        return to_route('crm.contacts.index')->with('success', 'Contact deleted.');
    }
}
