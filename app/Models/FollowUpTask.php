<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FollowUpTaskStatus;
use App\Enums\TaskPriority;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Override;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @property string $uuid
 * @property string $title
 * @property FollowUpTaskStatus $status
 * @property TaskPriority $priority
 * @property Carbon $due_at
 * @property ?Carbon $reminder_sent_at
 */
#[Fillable(['lead_id', 'assigned_to', 'title', 'description', 'status', 'priority', 'due_at', 'completed_at', 'reminder_sent_at'])]
class FollowUpTask extends Model
{
    /** @use HasFactory<\Database\Factories\FollowUpTaskFactory> */
    use HasFactory;

    use HasUuids;
    use LogsActivity;
    use SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('crm')
            ->logOnly(['lead_id', 'assigned_to', 'title', 'status', 'priority', 'due_at', 'completed_at'])
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

    /** @return BelongsTo<Lead, $this> */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /** @return BelongsTo<User, $this> */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /** @return array<string, string> */
    #[Override]
    protected function casts(): array
    {
        return [
            'status' => FollowUpTaskStatus::class,
            'priority' => TaskPriority::class,
            'due_at' => 'datetime',
            'completed_at' => 'datetime',
            'reminder_sent_at' => 'datetime',
        ];
    }

    /** @param Builder<static> $query */
    #[Scope]
    protected function overdue(Builder $query): void
    {
        $query->where('status', FollowUpTaskStatus::Open)->where('due_at', '<', now());
    }

    /** @param Builder<static> $query */
    #[Scope]
    protected function dueSoon(Builder $query): void
    {
        $query->where('status', FollowUpTaskStatus::Open)->whereBetween('due_at', [now(), now()->addDays(3)]);
    }
}
