<?php

declare(strict_types=1);

namespace App\Http\Requests\Development;

use App\Data\DevelopmentTaskProgressData;
use App\Models\DevelopmentTask;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDevelopmentTaskProgressRequest extends FormRequest
{
    public function authorize(): bool
    {
        $task = $this->route('developmentTask');

        return $task instanceof DevelopmentTask
            && $this->user()?->can('updateProgress', $task) === true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return DevelopmentTaskProgressData::rules();
    }

    public function toDto(): DevelopmentTaskProgressData
    {
        return DevelopmentTaskProgressData::fromValidated($this->validated());
    }
}
