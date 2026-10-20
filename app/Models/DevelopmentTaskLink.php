<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DevelopmentTaskLinkType;
use App\Support\WorkspaceUsers;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property string $uuid
 * @property DevelopmentTaskLinkType $type
 * @property ?string $label
 * @property string $url
 * @property DevelopmentTask $task
 * @property ?User $addedBy
 */
#[Fillable(['development_task_id', 'added_by_id', 'type', 'label', 'url'])]
class DevelopmentTaskLink extends Model
{
    /** @use HasFactory<\Database\Factories\DevelopmentTaskLinkFactory> */
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
    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(WorkspaceUsers::model(), 'added_by_id');
    }

    /** @return array<string, string> */
    #[Override]
    protected function casts(): array
    {
        return [
            'type' => DevelopmentTaskLinkType::class,
        ];
    }
}
