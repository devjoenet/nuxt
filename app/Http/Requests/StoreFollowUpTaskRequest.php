<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\TaskPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFollowUpTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        $lead = $this->route('lead');
        abort_unless($lead instanceof \App\Models\Lead && $lead->isVisibleTo($this->user()), 404);

        return $this->user()?->can('update', $lead) === true;
    }

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
