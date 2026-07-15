<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeadNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $lead = $this->route('lead');
        abort_unless($lead instanceof \App\Models\Lead && $lead->isVisibleTo($this->user()), 404);

        return $this->user()?->can('update', $lead) === true;
    }

    public function rules(): array
    {
        return ['body' => ['required', 'string', 'max:5000']];
    }
}
