<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePublicIntakeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array|string> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'lowercase', 'email:rfc', 'max:255'],
            'company' => ['nullable', 'string', 'max:160'],
            'phone' => ['nullable', 'string', 'max:40', 'regex:/^[0-9+().\-\s]+$/'],
            'website' => ['nullable', 'url:http,https', 'max:255'],
            'budget_range' => ['nullable', 'string', 'in:under-10k,10k-25k,25k-50k,50k-plus,not-sure'],
            'timeline' => ['nullable', 'string', 'in:urgent,1-3-months,3-6-months,exploring'],
            'service_interest' => ['nullable', 'string', 'in:operations-app,dashboard-portal,integration,reporting,modernization,other'],
            'referral_source' => ['nullable', 'string', 'max:160'],
            'project_details' => ['required', 'string', 'min:20', 'max:5000'],
            'consent' => ['accepted'],
            'website_confirmation' => ['prohibited'],
            'landing_page' => ['nullable', 'string', 'max:255'],
            'referrer' => ['nullable', 'url:http,https', 'max:2000'],
            'utm_source' => ['nullable', 'string', 'max:160'],
            'utm_medium' => ['nullable', 'string', 'max:160'],
            'utm_campaign' => ['nullable', 'string', 'max:160'],
            'utm_term' => ['nullable', 'string', 'max:160'],
            'utm_content' => ['nullable', 'string', 'max:160'],
        ];
    }
}
