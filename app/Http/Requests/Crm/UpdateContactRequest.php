<?php

declare(strict_types=1);

namespace App\Http\Requests\Crm;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use SoutheastCode\Crm\Contacts\Models\Contact;
use SoutheastCode\Crm\Shared\Queries\CrmOwnerOptions;
use SoutheastCode\Crm\Shared\Support\CrmAccess;

class UpdateContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $contact = $this->route('contact');
        if (! $user instanceof User) {
            return false;
        }

        abort_unless($contact instanceof Contact && $contact->isVisibleTo($user), 404);

        return $user->can('update', $contact);
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'organization_id' => ['nullable', 'uuid'],
            'owner_id' => [
                Rule::prohibitedIf(fn (): bool => ! CrmAccess::administrator($this->user())),
                'nullable',
                'integer',
                CrmOwnerOptions::assignmentRule($this->route('contact') instanceof Contact ? $this->route('contact')->owner_id : null),
            ],
        ];
    }
}
