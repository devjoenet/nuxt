<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\WorkspaceUsers;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Override;

/**
 * @property ?Carbon $tested_at
 * @property ?Carbon $approved_at
 * @property ?Carbon $roadmap_task_at
 */
#[Fillable([
    'slug',
    'category',
    'title',
    'route_name',
    'path',
    'sort_order',
    'tested_by',
    'tested_at',
    'approved_at',
    'roadmap_task_at',
    'notes',
])]
class UserTestingPage extends Model
{
    /** @use HasFactory<\Database\Factories\UserTestingPageFactory> */
    use HasFactory;

    /** @return BelongsTo<User, $this> */
    public function testedBy(): BelongsTo
    {
        return $this->belongsTo(WorkspaceUsers::model(), 'tested_by');
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'tested_at' => 'datetime',
            'approved_at' => 'datetime',
            'roadmap_task_at' => 'datetime',
        ];
    }
}
