<?php

declare(strict_types=1);

namespace App\Http\Requests\Crm;

use App\Enums\CrmSortDirection;
use App\Enums\LeadSort;
use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Models\Lead;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::enum(LeadStatus::class)],
            'source' => ['nullable', Rule::enum(LeadSource::class)],
            'owner' => ['nullable', 'integer', Rule::exists('users', 'id')->where(fn (Builder $query): Builder => $query->where('is_active', true))],
            'archived' => ['nullable', 'boolean'],
            'sort' => ['nullable', Rule::enum(LeadSort::class)],
            'direction' => ['nullable', Rule::enum(CrmSortDirection::class)],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'search' => $this->normalizedString('search'),
            'status' => $this->normalizedString('status'),
            'source' => $this->normalizedString('source'),
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
