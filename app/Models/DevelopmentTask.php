<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DevelopmentRequestType;
use App\Enums\DevelopmentTaskArea;
use App\Enums\DevelopmentTaskStatus;
use App\Enums\TaskPriority;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Override;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @property string $uuid
 * @property DevelopmentRequestType $request_type
 * @property DevelopmentTaskArea $affected_area
 * @property DevelopmentTaskStatus $status
 * @property TaskPriority $priority
 * @property ?int $submitter_id
 * @property ?int $reviewer_id
 * @property ?int $assignee_id
 * @property Carbon $submitted_at
 * @property ?Carbon $information_requested_at
 * @property ?Carbon $review_started_at
 * @property ?Carbon $planned_at
 * @property ?Carbon $started_at
 * @property ?Carbon $blocked_at
 * @property ?Carbon $ready_for_testing_at
 * @property ?Carbon $changes_requested_at
 * @property ?Carbon $completed_at
 * @property ?Carbon $rejected_at
 * @property ?Carbon $cancelled_at
 * @property ?Carbon $deleted_at
 * @property ?User $submitter
 * @property ?User $reviewer
 * @property ?User $assignee
 * @property-read \Illuminate\Database\Eloquent\Collection<int, DevelopmentTaskNote> $notes
 * @property-read \Illuminate\Database\Eloquent\Collection<int, DevelopmentTaskLink> $links
 * @property-read \Illuminate\Database\Eloquent\Collection<int, DevelopmentTestRun> $testRuns
 */
#[Fillable([
    'title', 'request_type', 'affected_area', 'description', 'desired_outcome', 'acceptance_criteria', 'status',
    'priority', 'roadmap_position', 'submitter_id', 'reviewer_id', 'assignee_id', 'submitted_at',
    'information_requested_at', 'review_started_at', 'planned_at', 'started_at', 'blocked_at',
    'ready_for_testing_at', 'changes_requested_at', 'completed_at', 'rejected_at', 'cancelled_at',
])]
class DevelopmentTask extends Model
{
    /** @use HasFactory<\Database\Factories\DevelopmentTaskFactory> */
    use HasFactory;

    use HasUuids;
    use LogsActivity;
    use SoftDeletes;

    /** @var array<string, string> */
    protected $attributes = [
        'status' => DevelopmentTaskStatus::Submitted->value,
        'priority' => TaskPriority::Normal->value,
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('development')
            ->logOnly([
                'status', 'priority', 'roadmap_position', 'reviewer_id', 'assignee_id', 'submitted_at',
                'information_requested_at', 'review_started_at', 'planned_at', 'started_at', 'blocked_at',
                'ready_for_testing_at', 'changes_requested_at', 'completed_at', 'rejected_at', 'cancelled_at',
            ])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

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

    /** @return BelongsTo<User, $this> */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitter_id');
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /** @return BelongsTo<User, $this> */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    /** @return HasMany<DevelopmentTaskNote, $this> */
    public function notes(): HasMany
    {
        return $this->hasMany(DevelopmentTaskNote::class);
    }

    /** @return HasMany<DevelopmentTaskLink, $this> */
    public function links(): HasMany
    {
        return $this->hasMany(DevelopmentTaskLink::class);
    }

    /** @return HasMany<DevelopmentTestRun, $this> */
    public function testRuns(): HasMany
    {
        return $this->hasMany(DevelopmentTestRun::class);
    }

    /** @return array<string, string> */
    #[Override]
    protected function casts(): array
    {
        return [
            'request_type' => DevelopmentRequestType::class,
            'affected_area' => DevelopmentTaskArea::class,
            'status' => DevelopmentTaskStatus::class,
            'priority' => TaskPriority::class,
            'submitted_at' => 'datetime',
            'information_requested_at' => 'datetime',
            'review_started_at' => 'datetime',
            'planned_at' => 'datetime',
            'started_at' => 'datetime',
            'blocked_at' => 'datetime',
            'ready_for_testing_at' => 'datetime',
            'changes_requested_at' => 'datetime',
            'completed_at' => 'datetime',
            'rejected_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }
}
