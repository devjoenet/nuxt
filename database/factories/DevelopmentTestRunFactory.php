<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\DevelopmentTestRunStatus;
use App\Models\DevelopmentTask;
use App\Models\DevelopmentTestRun;
use App\Support\WorkspaceUsers;
use Illuminate\Database\Eloquent\Factories\Factory;

use function fake;
use function now;

/** @extends Factory<DevelopmentTestRun> */
class DevelopmentTestRunFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'development_task_id' => DevelopmentTask::factory()->readyForTesting(),
            'tester_id' => WorkspaceUsers::model()::factory(),
            'requested_by_id' => WorkspaceUsers::model()::factory(),
            'instructions' => fake()->paragraph(),
            'build_reference' => fake()->url(),
            'status' => DevelopmentTestRunStatus::Pending,
            'feedback' => null,
            'requested_at' => now(),
            'completed_at' => null,
        ];
    }

    public function accepted(): static
    {
        return $this->state([
            'status' => DevelopmentTestRunStatus::Accepted,
            'feedback' => fake()->sentence(),
            'completed_at' => now(),
        ]);
    }

    public function changesRequested(): static
    {
        return $this->state([
            'status' => DevelopmentTestRunStatus::ChangesRequested,
            'feedback' => fake()->paragraph(),
            'completed_at' => now(),
        ]);
    }
}
