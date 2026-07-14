<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBusinessSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('settings.manage') ?? false;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:255'],
            'notification_email' => ['required', 'email', 'max:255'],
            'default_task_due_days' => ['required', 'integer', 'min:1', 'max:90'],
        ];
    }
}
