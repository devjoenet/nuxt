<?php

declare(strict_types=1);

namespace App\Http\Requests\Crm;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use SoutheastCode\Crm\Organizations\Models\Organization;
use SoutheastCode\Crm\Shared\Queries\CrmOwnerOptions;
use SoutheastCode\Crm\Shared\Support\CrmAccess;

class UpdateOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $organization = $this->route('organization');
        if (! $user instanceof User) {
            return false;
        }

        abort_unless($organization instanceof Organization && $organization->isVisibleTo($user), 404);

        return $user->can('update', $organization);
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        $organization = $this->route('organization');

        return [
            'name' => ['required', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255', Rule::unique('organizations', 'website')->ignore($organization)],
            'owner_id' => [
                Rule::prohibitedIf(fn (): bool => ! CrmAccess::administrator($this->user())),
                'nullable',
                'integer',
                CrmOwnerOptions::assignmentRule($this->route('organization') instanceof Organization ? $this->route('organization')->owner_id : null),
            ],
        ];
    }
}
