<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DevelopmentTestRunStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Override;

/**
 * @property string $uuid
 * @property int $development_task_id
 * @property ?int $tester_id
 * @property ?int $requested_by_id
 * @property string $instructions
 * @property ?string $build_reference
 * @property DevelopmentTestRunStatus $status
 * @property ?string $feedback
 * @property Carbon $requested_at
 * @property ?Carbon $completed_at
 * @property DevelopmentTask $task
 * @property ?User $tester
 * @property ?User $requestedBy
 */
#[Fillable([
    'development_task_id', 'tester_id', 'requested_by_id', 'instructions', 'build_reference', 'status',
    'feedback', 'requested_at', 'completed_at',
])]
class DevelopmentTestRun extends Model
{
    /** @use HasFactory<\Database\Factories\DevelopmentTestRunFactory> */
    use HasFactory;

    use HasUuids;

    /** @var array<string, string> */
    protected $attributes = [
        'status' => DevelopmentTestRunStatus::Pending->value,
    ];

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
    public function tester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tester_id');
    }

    /** @return BelongsTo<User, $this> */
    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_id');
    }

    /** @return array<string, string> */
    #[Override]
    protected function casts(): array
    {
        return [
            'status' => DevelopmentTestRunStatus::class,
            'requested_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }
}
