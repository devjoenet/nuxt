<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasCrmOwnership;
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
 * @property ?Carbon $archived_at
 * @property Carbon $created_at
 */
#[Fillable(['organization_id', 'owner_id', 'name', 'email', 'phone', 'archived_at'])]
class Contact extends Model
{
    use HasCrmOwnership;

    /** @use HasFactory<\Database\Factories\ContactFactory> */
    use HasFactory;

    use HasUuids;
    use LogsActivity;
    use SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('crm')
            ->logOnly(['organization_id', 'owner_id', 'name', 'archived_at'])
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

    /** @return BelongsTo<Organization, $this> */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /** @return HasMany<Lead, $this> */
    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    /** @return array<string, string> */
    #[Override]
    protected function casts(): array
    {
        return ['archived_at' => 'datetime'];
    }
}
