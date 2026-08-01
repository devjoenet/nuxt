<?php

declare(strict_types=1);

namespace App\Http\Requests\Development;

use App\Data\DevelopmentTestResultData;
use App\Models\DevelopmentTask;
use App\Models\DevelopmentTestRun;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecordDevelopmentTestResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        $testRun = $this->route('developmentTestRun');

        return $testRun instanceof DevelopmentTestRun
            && $this->user()?->can('update', $testRun) === true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        $testRun = $this->route('developmentTestRun');
        $task = $testRun instanceof DevelopmentTestRun ? $testRun->task : null;

        return [
            ...DevelopmentTestResultData::rules(),
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

    public function toDto(): DevelopmentTestResultData
    {
        return DevelopmentTestResultData::fromValidated($this->validated());
    }
}
