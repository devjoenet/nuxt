<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.roles.manage') ?? false;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => [
                'integer',
                'distinct:strict',
                Rule::exists('permissions', 'id')->where(fn (Builder $query): Builder => $query->where('guard_name', 'web')),
            ],
        ];
    }
}
