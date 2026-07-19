<?php

declare(strict_types=1);

namespace App\Http\Requests\Crm;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $contact = $this->route('contact');
        if (! $user instanceof User) {
            return false;
        }

        abort_unless($contact instanceof Contact && $contact->isVisibleTo($user), 404);

        return $user->can('update', $contact);
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        $contact = $this->route('contact');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('contacts', 'email')->ignore($contact)],
            'phone' => ['nullable', 'string', 'max:255'],
            'organization_id' => ['nullable', 'integer', 'exists:organizations,id'],
            'owner_id' => [
                Rule::prohibitedIf(fn (): bool => ! $this->user()?->isCrmAdministrator()),
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn (Builder $query): Builder => $query->where('is_active', true)),
            ],
        ];
    }
}
