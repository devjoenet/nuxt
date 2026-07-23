<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Spatie\Activitylog\Models\Activity;

class AuditIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Activity::class) === true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'event' => ['nullable', 'array', 'list'],
            'event.*' => ['required', 'string', 'max:255', 'distinct:strict'],
            'causer' => ['nullable', 'array', 'list'],
            'causer.*' => ['required', 'string', 'max:255', 'distinct:strict'],
            'subject' => ['nullable', 'array', 'list'],
            'subject.*' => ['required', 'string', 'max:255', 'distinct:strict'],
            'from' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:to'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'search' => $this->normalizedString('search'),
            'event' => $this->normalizedTokens('event'),
            'causer' => $this->normalizedTokens('causer'),
            'subject' => $this->normalizedTokens('subject'),
            'from' => $this->normalizedString('from'),
            'to' => $this->normalizedString('to'),
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

    private function normalizedTokens(string $key): mixed
    {
        $value = $this->input($key);

        if (is_string($value)) {
            $value = [$value];
        }

        if (! is_array($value)) {
            return $value;
        }

        return array_values(array_map(
            static fn (mixed $token): mixed => is_string($token) ? mb_trim($token) : $token,
            $value,
        ));
    }
}
