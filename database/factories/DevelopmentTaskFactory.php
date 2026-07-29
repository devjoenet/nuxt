<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\DevelopmentRequestType;
use App\Enums\DevelopmentTaskArea;
use App\Enums\DevelopmentTaskStatus;
use App\Enums\TaskPriority;
use App\Models\DevelopmentTask;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

use function fake;
use function now;

/** @extends Factory<DevelopmentTask> */
class DevelopmentTaskFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(6),
            'request_type' => fake()->randomElement(DevelopmentRequestType::cases()),
            'affected_area' => fake()->randomElement(DevelopmentTaskArea::cases()),
            'description' => fake()->paragraphs(2, true),
            'desired_outcome' => fake()->paragraph(),
            'acceptance_criteria' => fake()->sentences(3, true),
            'status' => DevelopmentTaskStatus::Submitted,
            'priority' => TaskPriority::Normal,
            'roadmap_position' => null,
            'submitter_id' => User::factory(),
            'reviewer_id' => null,
            'assignee_id' => null,
            'submitted_at' => now(),
        ];
    }

    public function needsInformation(): static
    {
        return $this->state([
            'status' => DevelopmentTaskStatus::NeedsInformation,
            'information_requested_at' => now(),
        ]);
    }

    public function blocked(): static
    {
        return $this->inProgress()->state([
            'status' => DevelopmentTaskStatus::Blocked,
            'blocked_at' => now(),
        ]);
    }

    public function inProgress(): static
    {
        return $this->planned()->state([
            'status' => DevelopmentTaskStatus::InProgress,
            'assignee_id' => User::factory(),
            'started_at' => now(),
        ]);
    }

    public function planned(): static
    {
        return $this->underReview()->state([
            'status' => DevelopmentTaskStatus::Planned,
            'planned_at' => now(),
        ]);
    }

    public function underReview(): static
    {
        return $this->state([
            'status' => DevelopmentTaskStatus::UnderReview,
            'reviewer_id' => User::factory(),
            'review_started_at' => now(),
        ]);
    }

    public function changesRequested(): static
    {
        return $this->readyForTesting()->state([
            'status' => DevelopmentTaskStatus::ChangesRequested,
            'changes_requested_at' => now(),
        ]);
    }

    public function readyForTesting(): static
    {
        return $this->inProgress()->state([
            'status' => DevelopmentTaskStatus::ReadyForTesting,
            'ready_for_testing_at' => now(),
        ]);
    }

    public function completed(): static
    {
        return $this->readyForTesting()->state([
            'status' => DevelopmentTaskStatus::Completed,
            'completed_at' => now(),
            'deleted_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->underReview()->state([
            'status' => DevelopmentTaskStatus::Rejected,
            'rejected_at' => now(),
            'deleted_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state([
            'status' => DevelopmentTaskStatus::Cancelled,
            'cancelled_at' => now(),
            'deleted_at' => now(),
        ]);
    }
}
