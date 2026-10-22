<?php

declare(strict_types=1);

namespace App\Http\Requests\Crm;

use App\Support\WorkspaceUsers;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use SoutheastCode\Crm\Contacts\Enums\ContactSort;
use SoutheastCode\Crm\Contacts\Models\Contact;
use SoutheastCode\Crm\Shared\Enums\CrmSortDirection;
use SoutheastCode\Crm\Shared\Http\CrmFilterInput;

class ContactIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Contact::class) === true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'organization' => ['nullable', 'uuid'],
            'owner' => ['nullable', 'integer', Rule::exists(WorkspaceUsers::model(), 'id')],
            'archived' => ['nullable', 'boolean'],
            'sort' => ['nullable', Rule::enum(ContactSort::class)],
            'direction' => ['nullable', Rule::enum(CrmSortDirection::class)],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(CrmFilterInput::normalize($this->all(), ['search', 'organization', 'owner', 'sort', 'direction']));
    }
}
