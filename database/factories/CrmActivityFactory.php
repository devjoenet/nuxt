<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CrmActivity;
use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CrmActivity> */
class CrmActivityFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'lead_id' => Lead::factory(),
            'type' => 'note_added',
            'description' => 'A follow-up note was added.',
            'properties' => [],
        ];
    }
}
