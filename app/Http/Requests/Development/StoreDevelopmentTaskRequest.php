<?php

declare(strict_types=1);

namespace App\Http\Requests\Development;

use App\Data\DevelopmentTaskData;
use App\Models\DevelopmentTask;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

class StoreDevelopmentTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', DevelopmentTask::class) === true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return Arr::except(DevelopmentTaskData::rules(), ['priority']);
    }

    public function toDto(): DevelopmentTaskData
    {
        return DevelopmentTaskData::fromValidated($this->validated());
    }

    protected function prepareForValidation(): void
    {
        $this->merge(collect([
            'title',
            'request_type',
            'affected_area',
            'description',
            'desired_outcome',
            'acceptance_criteria',
        ])->mapWithKeys(fn (string $key): array => [
            $key => is_string($this->input($key))
                ? mb_trim($this->string($key)->toString())
                : $this->input($key),
        ])->all());
    }
}
