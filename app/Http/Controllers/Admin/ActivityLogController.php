<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Activity::class);
        $filters = [
            'search' => $request->string('search')->trim()->value() ?: null,
            'event' => $request->string('event')->trim()->value() ?: null,
            'causer' => $request->string('causer')->trim()->value() ?: null,
        ];

        $activities = Activity::query()
            ->with(['causer', 'subject'])
            ->when($filters['search'] ?? null, fn (Builder $query, string $search): Builder => $query->where(function (Builder $query) use ($search): void {
                $query->where('description', 'like', "%{$search}%")
                    ->orWhere('log_name', 'like', "%{$search}%");
            }))
            ->when($filters['event'] ?? null, fn (Builder $query, string $event): Builder => $query->where('event', $event))
            ->when($filters['causer'] ?? null, fn (Builder $query, string $causer): Builder => $query->whereHasMorph('causer', [User::class], fn (Builder $query): Builder => $query->where('name', 'like', "%{$causer}%")))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('Admin/Audit/Index', [
            'activities' => $activities->through(function (mixed $activity): array {
                /** @var Activity $activity */
                $causer = $activity->causer;

                return [
                    'id' => $activity->id,
                    'description' => $activity->description,
                    'event' => $activity->event,
                    'log_name' => $activity->log_name,
                    'causer' => $causer instanceof User ? $causer->name : null,
                    'subject' => $activity->subject ? class_basename($activity->subject) : null,
                    'changes' => $activity->attribute_changes?->toArray(),
                    'created_at' => $activity->created_at?->toISOString(),
                ];
            }),
            'filters' => $filters,
        ]);
    }
}
