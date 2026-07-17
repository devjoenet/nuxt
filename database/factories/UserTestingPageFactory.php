<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\UserTestingPage;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<UserTestingPage> */
class UserTestingPageFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'slug' => fake()->unique()->slug(),
            'category' => fake()->randomElement(['public', 'workspace', 'admin']),
            'title' => fake()->sentence(3),
            'route_name' => fake()->optional()->word(),
            'path' => '/'.fake()->slug(2),
            'sort_order' => fake()->numberBetween(1, 30),
            'tested_by' => null,
            'tested_at' => null,
            'notes' => null,
        ];
    }
}
