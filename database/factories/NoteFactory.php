<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Lead;
use App\Models\Note;
use App\Support\WorkspaceUsers;
use Illuminate\Database\Eloquent\Factories\Factory;

use function fake;

/** @extends Factory<Note> */
class NoteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'subject_type' => Lead::class,
            'subject_id' => Lead::factory(),
            'user_id' => WorkspaceUsers::model()::factory(),
            'body' => fake()->paragraph(),
        ];
    }
}
