<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\DevelopmentNoteAudience;
use App\Models\DevelopmentTask;
use App\Models\DevelopmentTaskNote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

use function fake;

/** @extends Factory<DevelopmentTaskNote> */
class DevelopmentTaskNoteFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'development_task_id' => DevelopmentTask::factory(),
            'author_id' => User::factory(),
            'audience' => DevelopmentNoteAudience::Participants,
            'body' => fake()->paragraph(),
        ];
    }

    public function internal(): static
    {
        return $this->state([
            'audience' => DevelopmentNoteAudience::DevelopmentTeam,
        ]);
    }
}
