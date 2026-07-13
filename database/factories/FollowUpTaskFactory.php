<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\FollowUpTask;
use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

use function now;

/** @extends Factory<FollowUpTask> */
class FollowUpTaskFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'lead_id' => Lead::factory(),
            'title' => 'Follow up on inquiry',
            'description' => 'Review the project details and contact the lead.',
            'status' => 'open',
            'due_at' => now()->addWeekday()->setTime(17, 0),
        ];
    }
}
