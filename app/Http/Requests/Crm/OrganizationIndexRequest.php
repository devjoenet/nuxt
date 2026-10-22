<?php

declare(strict_types=1);

namespace App\Http\Requests\Crm;

use App\Support\WorkspaceUsers;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use SoutheastCode\Crm\Organizations\Enums\OrganizationSort;
use SoutheastCode\Crm\Organizations\Models\Organization;
use SoutheastCode\Crm\Shared\Enums\CrmSortDirection;
use SoutheastCode\Crm\Shared\Http\CrmFilterInput;

class OrganizationIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Organization::class) === true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'owner' => ['nullable', 'integer', Rule::exists(WorkspaceUsers::model(), 'id')],
            'archived' => ['nullable', 'boolean'],
            'sort' => ['nullable', Rule::enum(OrganizationSort::class)],
            'direction' => ['nullable', Rule::enum(CrmSortDirection::class)],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(CrmFilterInput::normalize($this->all(), ['search', 'owner', 'sort', 'direction']));
    }
}
