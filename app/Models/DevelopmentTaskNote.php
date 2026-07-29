<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DevelopmentNoteAudience;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property string $uuid
 * @property DevelopmentNoteAudience $audience
 * @property DevelopmentTask $task
 * @property ?User $author
 */
#[Fillable(['development_task_id', 'author_id', 'audience', 'body'])]
class DevelopmentTaskNote extends Model
{
    /** @use HasFactory<\Database\Factories\DevelopmentTaskNoteFactory> */
    use HasFactory;

    use HasUuids;

    #[Override]
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /** @return array<int, string> */
    #[Override]
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    /** @return BelongsTo<DevelopmentTask, $this> */
    public function task(): BelongsTo
    {
        return $this->belongsTo(DevelopmentTask::class, 'development_task_id');
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /** @return array<string, string> */
    #[Override]
    protected function casts(): array
    {
        return [
            'audience' => DevelopmentNoteAudience::class,
        ];
    }
}
