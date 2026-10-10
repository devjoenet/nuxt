<?php

declare(strict_types=1);

namespace App\Http\Controllers\Crm;

use App\Actions\ArchiveContact;
use App\Actions\CreateContact;
use App\Actions\DeleteContact;
use App\Actions\RestoreContact;
use App\Actions\UpdateContact;
use App\Data\ContactData;
use App\Data\ContactIndexData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\ContactIndexRequest;
use App\Http\Requests\Crm\StoreContactRequest;
use App\Http\Requests\Crm\UpdateContactRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use App\Models\Lead;
use App\Models\User;
use App\Queries\ContactQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class ContactController extends Controller
{
    public function index(ContactIndexRequest $request, ContactQuery $query): Response
    {
        $user = $this->authenticatedUser($request);
        $filters = ContactIndexData::fromValidated($request->validated());

        return Inertia::render('Crm/Contacts/Index', [
            'contacts' => ContactResource::collection($query->build($user, $filters)->paginate(15)->withQueryString()),
            'filters' => $filters->filters(),
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
        $contact->load(['organization', 'owner'])->loadCount([
            'leads' => function (Builder $query) use ($user): void {
                Lead::constrainVisibility($query, $user);
            },
        ]);

        return Inertia::render('Crm/Contacts/Show', [
            'contact' => ContactResource::make($contact)->resolve($request),
            'canManage' => $user->can('crm.manage'),
            'canViewHistory' => $user->can('viewAny', Activity::class),
        ]);
    }

    public function edit(Request $request, Contact $contact): Response
    {
        $user = $this->authenticatedUser($request);
        abort_unless($contact->isVisibleTo($user), 404);
        $this->authorize('update', $contact);

        return Inertia::render('Crm/Contacts/Form', [
            'contact' => ContactResource::make($contact->load(['organization', 'owner']))->resolve($request),
            'owners' => $this->owners(),
            'canAssign' => $user->isCrmAdministrator(),
        ]);
    }

    public function archive(Request $request, Contact $contact, ArchiveContact $action): RedirectResponse
    {
        $user = $this->authenticatedUser($request);
        abort_unless($contact->isVisibleTo($user), 404);
        $this->authorize('archive', $contact);
        $action->handle($contact, $user);

        return to_route('crm.contacts.index')->with('success', 'Contact archived.');
    }

    public function update(UpdateContactRequest $request, Contact $contact, UpdateContact $action): RedirectResponse
    {
        $contact = $action->handle($contact, ContactData::fromValidated($request->validated()), $this->authenticatedUser($request));

        return to_route('crm.contacts.show', $contact)->with('success', 'Contact updated.');
    }

    public function restore(Request $request, Contact $contact, RestoreContact $action): RedirectResponse
    {
        $user = $this->authenticatedUser($request);
        abort_unless($contact->isVisibleTo($user), 404);
        $this->authorize('restore', $contact);
        $action->handle($contact, $user);

        return to_route('crm.contacts.show', $contact)->with('success', 'Contact restored.');
    }

    public function destroy(Request $request, Contact $contact, DeleteContact $action): RedirectResponse
    {
        $user = $this->authenticatedUser($request);
        abort_unless($contact->isVisibleTo($user), 404);
        $this->authorize('delete', $contact);
        $action->handle($contact, $user);

        return to_route('crm.contacts.index')->with('success', 'Contact deleted.');
    }
}
