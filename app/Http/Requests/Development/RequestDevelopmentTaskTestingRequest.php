<?php

declare(strict_types=1);

namespace App\Http\Requests\Development;

use App\Data\RequestDevelopmentTaskTestingData;
use App\Models\DevelopmentTask;
use App\Models\DevelopmentTestRun;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RequestDevelopmentTaskTestingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $task = $this->route('developmentTask');

        return $task instanceof DevelopmentTask
            && $this->user()?->can('create', [DevelopmentTestRun::class, $task]) === true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        $task = $this->route('developmentTask');

        return [
            ...RequestDevelopmentTaskTestingData::rules(),
            'development_task_id' => [
                'required',
                'uuid',
                Rule::in($task instanceof DevelopmentTask ? [$task->uuid] : []),
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'development_task_id.required' => 'The task changed while this testing form was open. Reload the page and try again.',
            'development_task_id.in' => 'The task changed while this testing form was open. Reload the page and try again.',
        ];
    }

    public function toDto(): RequestDevelopmentTaskTestingData
    {
        return RequestDevelopmentTaskTestingData::fromValidated($this->validated());
    }
}
