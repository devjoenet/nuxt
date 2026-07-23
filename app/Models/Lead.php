<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Models\Concerns\HasCrmOwnership;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Override;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @property string $uuid
 * @property LeadStatus $status
 * @property LeadSource $source
 * @property ?Carbon $archived_at
 * @property ?Carbon $converted_at
 * @property Carbon $created_at
 * @property ?Contact $contact
 * @property ?Organization $organization
 * @property ?User $owner
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Note> $notes
 * @property-read \Illuminate\Database\Eloquent\Collection<int, CrmActivity> $activities
 * @property-read \Illuminate\Database\Eloquent\Collection<int, FollowUpTask> $followUpTasks
 */
#[Fillable([
    'contact_id', 'intake_snapshot', 'organization_id', 'owner_id', 'status', 'source', 'service_interest', 'service_interests', 'budget_range',
    'timeline', 'project_details', 'referral_source', 'landing_page', 'referrer', 'utm_source', 'utm_medium',
    'utm_campaign', 'utm_term', 'utm_content', 'ip_address', 'user_agent', 'consented_at', 'archived_at', 'converted_at',
])]
class Lead extends Model
{
    use HasCrmOwnership;

    /** @use HasFactory<\Database\Factories\LeadFactory> */
    use HasFactory;

    use HasUuids;
    use LogsActivity;
    use SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('crm')
            ->logOnly(['organization_id', 'owner_id', 'status', 'source', 'service_interest', 'service_interests', 'budget_range', 'timeline', 'archived_at', 'converted_at'])
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

    /** @return BelongsTo<Contact, $this> */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class)->withTrashed();
    }

    /** @return BelongsTo<Organization, $this> */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /** @return HasMany<CrmActivity, $this> */
    public function activities(): HasMany
    {
        return $this->hasMany(CrmActivity::class);
    }

    /** @return HasMany<FollowUpTask, $this> */
    public function followUpTasks(): HasMany
    {
        return $this->hasMany(FollowUpTask::class);
    }

    /** @return MorphMany<Note, $this> */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'subject');
    }

    /** @return array<string, string> */
    #[Override]
    protected function casts(): array
    {
        return [
            'status' => LeadStatus::class,
            'source' => LeadSource::class,
            'service_interests' => 'array',
            'intake_snapshot' => 'array',
            'consented_at' => 'datetime',
            'archived_at' => 'datetime',
            'converted_at' => 'datetime',
        ];
    }
}
