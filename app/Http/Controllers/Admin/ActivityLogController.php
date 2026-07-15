<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
            'subject' => $request->string('subject')->trim()->value() ?: null,
            'from' => $request->string('from')->trim()->value() ?: null,
            'to' => $request->string('to')->trim()->value() ?: null,
        ];
        $from = $this->parseDate($filters['from']);
        $to = $this->parseDate($filters['to']);

        $activities = Activity::query()
            ->with(['causer', 'subject'])
            ->when($filters['search'] ?? null, fn (Builder $query, string $search): Builder => $query->where(function (Builder $query) use ($search): void {
                $term = sprintf('%%%s%%', $search);

                $query->where('description', 'like', $term)
                    ->orWhere('log_name', 'like', $term)
                    ->orWhere('event', 'like', $term)
                    ->orWhere('subject_type', 'like', $term)
                    ->orWhere('subject_id', 'like', $term)
                    ->orWhere('causer_type', 'like', $term)
                    ->orWhere('causer_id', 'like', $term)
                    ->orWhereHasMorph('causer', [User::class], fn (Builder $query): Builder => $query
                        ->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term));
            }))
            ->when($filters['event'] ?? null, fn (Builder $query, string $event): Builder => $query->where('event', $event))
            ->when($filters['causer'] ?? null, fn (Builder $query, string $causer): Builder => $query->where(function (Builder $query) use ($causer): void {
                $term = sprintf('%%%s%%', $causer);

                $query->where('causer_type', 'like', $term)
                    ->orWhere('causer_id', 'like', $term)
                    ->orWhereHasMorph('causer', [User::class], fn (Builder $query): Builder => $query
                        ->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term));
            }))
            ->when($filters['subject'] ?? null, fn (Builder $query, string $subject): Builder => $query->where(function (Builder $query) use ($subject): void {
                $term = sprintf('%%%s%%', $subject);

                $query->where('subject_type', 'like', $term)
                    ->orWhere('subject_id', 'like', $term);
            }))
            ->when($from instanceof Carbon, fn (Builder $query): Builder => $query->where('created_at', '>=', $from->copy()->startOfDay()))
            ->when($to instanceof Carbon, fn (Builder $query): Builder => $query->where('created_at', '<=', $to->copy()->endOfDay()))
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

    private function parseDate(?string $date): ?Carbon
    {
        if ($date === null) {
            return null;
        }

        try {
            $parsed = Carbon::createFromFormat('!Y-m-d', $date);
        } catch (InvalidFormatException) {
            return null;
        }

        return $parsed instanceof Carbon ? $parsed : null;
    }
}
