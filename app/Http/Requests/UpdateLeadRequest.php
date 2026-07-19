<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\User;
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
        $lead = $this->route('lead');

        return [
            'contact_name' => ['required', 'string', 'max:255'],
            'contact_email' => ['required', 'email', 'max:255', Rule::unique('contacts', 'email')->ignore($lead instanceof Lead ? $lead->contact_id : null)],
            'contact_phone' => ['nullable', 'string', 'max:255'],
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
