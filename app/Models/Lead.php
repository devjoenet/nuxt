<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

#[Fillable([
    'contact_id', 'organization_id', 'owner_id', 'status', 'source', 'service_interest', 'budget_range',
    'timeline', 'project_details', 'referral_source', 'landing_page', 'referrer', 'utm_source', 'utm_medium',
    'utm_campaign', 'utm_term', 'utm_content', 'ip_address', 'user_agent', 'consented_at',
])]
class Lead extends Model
{
    /** @use HasFactory<\Database\Factories\LeadFactory> */
    use HasFactory;

    use HasUuids;

    /** @return array<int, string> */
    #[Override]
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    /** @return BelongsTo<Contact, $this> */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    /** @return BelongsTo<Organization, $this> */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
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

    /** @return array<string, string> */
    #[Override]
    protected function casts(): array
    {
        return ['consented_at' => 'datetime'];
    }
}
