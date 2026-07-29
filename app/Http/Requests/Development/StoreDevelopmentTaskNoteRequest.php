<?php

declare(strict_types=1);

namespace App\Http\Requests\Development;

use App\Data\DevelopmentTaskNoteData;
use App\Models\DevelopmentTask;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreDevelopmentTaskNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $task = $this->route('developmentTask');

        return $task instanceof DevelopmentTask
            && $this->user()?->can('view', $task) === true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return DevelopmentTaskNoteData::rules();
    }

    public function toDto(): DevelopmentTaskNoteData
    {
        return DevelopmentTaskNoteData::fromValidated($this->validated());
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('body'))) {
            $this->merge(['body' => mb_trim($this->string('body')->toString())]);
        }
    }
}
