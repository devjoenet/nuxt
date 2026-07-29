<?php

declare(strict_types=1);

namespace App\Http\Requests\Development;

use App\Data\UpdateDevelopmentTaskRoadmapData;
use App\Models\DevelopmentTask;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDevelopmentTaskRoadmapRequest extends FormRequest
{
    public function authorize(): bool
    {
        $task = $this->route('developmentTask');

        return $task instanceof DevelopmentTask
            && $this->user()?->can('manageRoadmap', $task) === true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return UpdateDevelopmentTaskRoadmapData::rules();
    }

    public function toDto(): UpdateDevelopmentTaskRoadmapData
    {
        return UpdateDevelopmentTaskRoadmapData::fromValidated($this->validated());
    }
}
