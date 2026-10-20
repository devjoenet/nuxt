<?php

declare(strict_types=1);

namespace App\Http\Requests\Crm;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Enums\ServiceInterest;
use App\Models\Lead;
use App\Models\User;
use App\Support\CrmAccess;
use App\Support\WorkspaceUsers;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $lead = $this->route('lead');
        if (! $user instanceof User) {
            return false;
        }

        abort_unless($lead instanceof Lead && $lead->isVisibleTo($user), 404);

        return $user->can('update', $lead);
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
                Rule::exists(WorkspaceUsers::model(), 'id')->where(fn (Builder $query): Builder => $query->where('is_active', true)),
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
