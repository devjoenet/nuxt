<?php

declare(strict_types=1);

namespace App\Http\Requests\Crm;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use SoutheastCode\Crm\Contacts\Models\Contact;
use SoutheastCode\Crm\Shared\Queries\CrmOwnerOptions;
use SoutheastCode\Crm\Shared\Support\CrmAccess;

class StoreContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Contact::class) === true;
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
                CrmOwnerOptions::assignmentRule(),
            ],
        ];
    }
}
