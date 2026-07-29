<?php

declare(strict_types=1);

namespace App\Http\Requests\Development;

use App\Data\DevelopmentTaskLinkData;
use App\Models\DevelopmentTask;
use App\Models\DevelopmentTaskLink;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreDevelopmentTaskLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        $task = $this->route('developmentTask');

        return $task instanceof DevelopmentTask
            && $this->user()?->can('create', [
                DevelopmentTaskLink::class,
                $task,
            ]) === true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return DevelopmentTaskLinkData::rules();
    }

    public function toDto(): DevelopmentTaskLinkData
    {
        return DevelopmentTaskLinkData::fromValidated($this->validated());
    }

    protected function prepareForValidation(): void
    {
        foreach (['label', 'url'] as $key) {
            if (is_string($this->input($key))) {
                $this->merge([$key => mb_trim($this->string($key)->toString())]);
            }
        }
    }
}
