<?php

declare(strict_types=1);

namespace App\Http\Requests\Crm;

use App\Enums\TaskPriority;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use SoutheastCode\Crm\Leads\Models\Lead;

class StoreFollowUpTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $lead = $this->route('lead');
        if (! $user instanceof User) {
            return false;
        }

        abort_unless($lead instanceof Lead && $lead->isVisibleTo($user), 404);

        return $user->can('update', $lead);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'priority' => ['required', Rule::enum(TaskPriority::class)],
            'due_at' => ['required', 'date', 'after:now'],
        ];
    }
}
