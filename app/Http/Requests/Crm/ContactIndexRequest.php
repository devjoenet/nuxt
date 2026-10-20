<?php

declare(strict_types=1);

namespace App\Http\Requests\Crm;

use App\Enums\ContactSort;
use App\Enums\CrmSortDirection;
use App\Models\Contact;
use App\Support\WorkspaceUsers;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'owner' => ['nullable', 'integer', Rule::exists(WorkspaceUsers::model(), 'id')->where(fn (Builder $query): Builder => $query->where('is_active', true))],
            'archived' => ['nullable', 'boolean'],
            'sort' => ['nullable', Rule::enum(ContactSort::class)],
            'direction' => ['nullable', Rule::enum(CrmSortDirection::class)],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'search' => $this->normalizedString('search'),
            'organization' => $this->normalizedString('organization'),
            'owner' => $this->normalizedString('owner'),
            'sort' => $this->normalizedString('sort'),
            'direction' => $this->normalizedString('direction'),
            'archived' => $this->normalizedArchived(),
        ]);
    }

    private function normalizedString(string $key): mixed
    {
        $value = $this->input($key);

        if (! is_string($value)) {
            return $value;
        }

        return filled(mb_trim($value)) ? mb_trim($value) : null;
    }

    private function normalizedArchived(): mixed
    {
        $value = $this->input('archived');

        if ($value === null || $value === '') {
            return false;
        }

        $normalized = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return $normalized ?? $value;
    }
}
