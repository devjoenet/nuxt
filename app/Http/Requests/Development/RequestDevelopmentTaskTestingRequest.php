<?php

declare(strict_types=1);

namespace App\Http\Requests\Development;

use App\Data\RequestDevelopmentTaskTestingData;
use App\Models\DevelopmentTask;
use App\Models\DevelopmentTestRun;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

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
        return RequestDevelopmentTaskTestingData::rules();
    }

    public function toDto(): RequestDevelopmentTaskTestingData
    {
        return RequestDevelopmentTaskTestingData::fromValidated($this->validated());
    }
}
