<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Contact;
use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

use function fake;
use function now;

/** @extends Factory<Lead> */
class LeadFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'contact_id' => Contact::factory(),
            'organization_id' => null,
            'status' => fake()->randomElement(['new', 'contacted', 'qualified']),
            'source' => fake()->randomElement(['website', 'referral']),
            'service_interest' => fake()->randomElement(['operations-app', 'dashboard-portal', 'integration', 'reporting']),
            'service_interests' => fn (array $attributes): array => [$attributes['service_interest']],
            'budget_range' => fake()->randomElement(['10k-25k', '25k-50k', '50k-plus']),
            'timeline' => fake()->randomElement(['1-3-months', '3-6-months', 'exploring']),
            'project_details' => fake()->paragraphs(2, true),
            'landing_page' => '/',
            'consented_at' => now(),
        ];
    }
}
