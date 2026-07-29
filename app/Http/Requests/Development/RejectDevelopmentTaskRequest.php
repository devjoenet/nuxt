<?php

declare(strict_types=1);

namespace App\Http\Requests\Development;

use App\Data\DevelopmentTaskNoteData;
use App\Enums\DevelopmentNoteAudience;
use App\Models\DevelopmentTask;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RejectDevelopmentTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        $task = $this->route('developmentTask');

        return $task instanceof DevelopmentTask
            && $this->user()?->can('review', $task) === true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:10000'],
        ];
    }

    public function toDto(): DevelopmentTaskNoteData
    {
        return new DevelopmentTaskNoteData(
            body: mb_trim($this->string('body')->toString()),
            audience: DevelopmentNoteAudience::Participants,
        );
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('body'))) {
            $this->merge(['body' => mb_trim($this->string('body')->toString())]);
        }
    }
}
