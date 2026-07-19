<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Models\Lead;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'contact_name' => ['required', 'string', 'max:255'],
            'contact_email' => ['required', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:255'],
            'organization_name' => ['nullable', 'string', 'max:255'],
            'organization_website' => ['nullable', 'url', 'max:255'],
            'owner_id' => [
                Rule::prohibitedIf(fn (): bool => ! $this->user()?->isCrmAdministrator()),
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn (Builder $query): Builder => $query->where('is_active', true)),
            ],
            'status' => ['required', Rule::enum(LeadStatus::class)],
            'source' => ['required', Rule::enum(LeadSource::class)],
            'service_interest' => ['nullable', 'array'],
            'service_interest.*' => ['string', Rule::in(['operations-app', 'dashboard-portal', 'integration', 'reporting', 'modernization', 'other'])],
            'budget_range' => ['nullable', 'string', 'max:255'],
            'timeline' => ['nullable', 'string', 'max:255'],
            'project_details' => ['required', 'string', 'max:10000'],
        ];
    }
}
