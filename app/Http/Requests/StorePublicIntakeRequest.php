<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ServiceInterest;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Override;

class StorePublicIntakeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public static function normalize(array $attributes): array
    {
        $budgetRange = $attributes['budget_range'] ?? null;

        if (! is_string($budgetRange)) {
            return $attributes;
        }

        $attributes['budget_range'] = str_replace([',', '$', ' '], '', mb_trim($budgetRange));

        return $attributes;
    }

    #[Override]
    protected function prepareForValidation(): void
    {
        $this->replace(self::normalize($this->all()));
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'lowercase', 'email:rfc', 'max:255'],
            'company' => ['nullable', 'string', 'max:160'],
            'phone' => ['nullable', 'string', 'max:40', 'regex:/^[0-9+().\-\s]+$/'],
            'website' => ['nullable', 'url:http,https', 'max:255'],
            'budget_range' => ['nullable', 'string', 'max:32', function (string $attribute, mixed $value, Closure $fail): void {
                if (in_array($value, ['under-10k', '10k-25k', '25k-50k', '50k-plus', 'not-sure'], true) || preg_match('/^\d{1,9}(?:\.\d{1,2})?$/', (string) $value) === 1) {
                    return;
                }

                $fail('Enter a valid budget amount.');
            }],
            'timeline' => ['nullable', 'string', 'in:urgent,1-3-months,3-6-months,exploring'],
            'service_interest' => ['nullable', 'array', 'list', 'max:6'],
            'service_interest.*' => ['string', 'distinct:strict', Rule::enum(ServiceInterest::class)],
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
