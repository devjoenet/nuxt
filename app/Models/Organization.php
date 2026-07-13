<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Override;

/** @property ?Carbon $client_since_at */
#[Fillable(['name', 'website', 'client_since_at', 'archived_at'])]
class Organization extends Model
{
    /** @use HasFactory<\Database\Factories\OrganizationFactory> */
    use HasFactory;

    use HasUuids;
    use SoftDeletes;

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
