<?php

declare(strict_types=1);

namespace App\Http\Requests\Crm;

use App\Support\WorkspaceUsers;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use SoutheastCode\Crm\Leads\Enums\LeadSort;
use SoutheastCode\Crm\Leads\Enums\LeadSource;
use SoutheastCode\Crm\Leads\Enums\LeadStatus;
use SoutheastCode\Crm\Leads\Models\Lead;
use SoutheastCode\Crm\Shared\Enums\CrmSortDirection;
use SoutheastCode\Crm\Shared\Http\CrmFilterInput;

class LeadIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Lead::class) === true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'unassigned' => ['nullable', 'boolean'],
            'overdue' => ['nullable', 'boolean'],
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::enum(LeadStatus::class)],
            'source' => ['nullable', Rule::enum(LeadSource::class)],
            'owner' => ['nullable', 'integer', Rule::exists(WorkspaceUsers::model(), 'id')],
            'archived' => ['nullable', 'boolean'],
            'sort' => ['nullable', Rule::enum(LeadSort::class)],
            'direction' => ['nullable', Rule::enum(CrmSortDirection::class)],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(CrmFilterInput::normalize($this->all(), ['search', 'status', 'source', 'owner', 'sort', 'direction']));
    }
}
