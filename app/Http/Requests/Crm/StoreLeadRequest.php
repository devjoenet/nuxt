<?php

declare(strict_types=1);

namespace App\Http\Requests\Crm;

use App\Enums\ServiceInterest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use SoutheastCode\Crm\Leads\Enums\LeadSource;
use SoutheastCode\Crm\Leads\Enums\LeadStatus;
use SoutheastCode\Crm\Leads\Models\Lead;
use SoutheastCode\Crm\Shared\Queries\CrmOwnerOptions;
use SoutheastCode\Crm\Shared\Support\CrmAccess;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Lead::class) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'contact_id' => ['required', 'uuid'],
            'organization_id' => ['nullable', 'uuid'],
            'owner_id' => [
                Rule::prohibitedIf(fn (): bool => ! CrmAccess::administrator($this->user())),
                'nullable',
                'integer',
                CrmOwnerOptions::assignmentRule(),
            ],
            'status' => ['required', Rule::enum(LeadStatus::class)],
            'source' => ['required', Rule::enum(LeadSource::class)],
            'service_interest' => ['nullable', 'array', 'list', 'max:6'],
            'service_interest.*' => ['string', 'distinct:strict', Rule::enum(ServiceInterest::class)],
            'budget_range' => ['nullable', 'string', 'max:255'],
            'timeline' => ['nullable', 'string', 'max:255'],
            'project_details' => ['required', 'string', 'max:10000'],
        ];
    }
}
