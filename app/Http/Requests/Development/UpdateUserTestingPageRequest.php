<?php

declare(strict_types=1);

namespace App\Http\Requests\Development;

use App\Enums\Permission;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserTestingPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(Permission::DevelopmentUserTestingManage->value) === true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'tested' => ['nullable', 'boolean'],
            'approved' => ['nullable', 'boolean'],
            'roadmap_task' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'required_if:roadmap_task,1', 'string', 'max:10000'],
        ];
    }
}
