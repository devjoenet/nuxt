<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Override;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable(['subject_type', 'subject_id', 'user_id', 'body'])]
class Note extends Model
{
    /** @use HasFactory<\Database\Factories\NoteFactory> */
    use HasFactory;

    use HasUuids;
    use LogsActivity;
    use SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('crm')
            ->logOnly(['subject_type', 'subject_id', 'user_id'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    /** @return array<int, string> */
    #[Override]
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    /** @return MorphTo<Model, $this> */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
