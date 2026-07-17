<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Permission;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserTestingPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(Permission::DevelopmentUserTestingView->value) === true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'tested' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
