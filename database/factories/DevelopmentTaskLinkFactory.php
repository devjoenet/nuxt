<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\DevelopmentTaskLinkType;
use App\Models\DevelopmentTask;
use App\Models\DevelopmentTaskLink;
use App\Support\WorkspaceUsers;
use Illuminate\Database\Eloquent\Factories\Factory;

use function fake;

/** @extends Factory<DevelopmentTaskLink> */
class DevelopmentTaskLinkFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'development_task_id' => DevelopmentTask::factory(),
            'added_by_id' => WorkspaceUsers::model()::factory(),
            'type' => DevelopmentTaskLinkType::External,
            'label' => fake()->words(3, true),
            'url' => fake()->url(),
        ];
    }
}
