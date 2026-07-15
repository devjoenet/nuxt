<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasCrmOwnership;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Override;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @property ?Carbon $client_since_at
 * @property ?Carbon $archived_at
 * @property Carbon $created_at
 */
#[Fillable(['name', 'website', 'owner_id', 'client_since_at', 'archived_at'])]
class Organization extends Model
{
    use HasCrmOwnership;

    /** @use HasFactory<\Database\Factories\OrganizationFactory> */
    use HasFactory;

    use HasUuids;
    use LogsActivity;
    use SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('crm')
            ->logOnly(['name', 'website', 'owner_id', 'client_since_at', 'archived_at'])
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

    /** @return HasMany<Contact, $this> */
    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    /** @return HasMany<Lead, $this> */
    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    #[Override]
    protected function casts(): array
    {
        return ['client_since_at' => 'datetime', 'archived_at' => 'datetime'];
    }
}
