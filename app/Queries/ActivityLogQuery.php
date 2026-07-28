<?php

declare(strict_types=1);

namespace App\Queries;

use App\Data\ActivityLogFiltersData;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;
use UnexpectedValueException;

final class ActivityLogQuery
{
    private const string UNSPECIFIED_EVENT = '__unspecified__';

    private const string SYSTEM_CAUSER = '__system__';

    private const string NO_SUBJECT = '__no_subject__';

    private const string FILTER_OPTIONS_CACHE_KEY = 'activity-log.filter-options.v1';

    /**
     * @return array{
     *     events: array<int, array{label: string, value: string}>,
     *     causers: array<int, array{label: string, value: string}>,
     *     subjects: array<int, array{label: string, value: string}>,
     * }
     */
    public function filterOptions(): array
    {
        return Cache::remember(self::FILTER_OPTIONS_CACHE_KEY, now()->addMinutes(5), function (): array {
            $events = Activity::query()->whereNotNull('event')->distinct()->pluck('event')->filter()->map(
                fn (mixed $event): array => ['label' => Str::headline((string) $event), 'value' => (string) $event],
            )->values()->all();

            if (Activity::query()->whereNull('event')->exists()) {
                array_unshift($events, ['label' => 'Unspecified', 'value' => self::UNSPECIFIED_EVENT]);
            }

            $causerIds = Activity::query()
                ->where('causer_type', User::class)
                ->whereNotNull('causer_id')
                ->distinct()
                ->pluck('causer_id')
                ->map(fn (mixed $id): string => (string) $id)
                ->values();
            $users = User::query()->whereIn((new User)->getKeyName(), $causerIds->all())->get(['id', 'name']);
            $nameCounts = $users->countBy('name');
            $causers = $users->map(fn (User $user): array => [
                'label' => $nameCounts->get($user->name, 0) > 1 ? "{$user->name} (#{$user->getKey()})" : $user->name,
                'value' => (string) $user->getKey(),
            ])->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE)->values()->all();

            if (Activity::query()->whereNull('causer_id')->exists()) {
                array_unshift($causers, ['label' => 'System', 'value' => self::SYSTEM_CAUSER]);
            }

            $subjects = Activity::query()->whereNotNull('subject_type')->distinct()->pluck('subject_type')->filter()->map(
                fn (mixed $subject): array => ['label' => Str::headline(class_basename((string) $subject)), 'value' => (string) $subject],
            )->values()->all();

            if (Activity::query()->whereNull('subject_type')->exists()) {
                array_unshift($subjects, ['label' => 'No subject', 'value' => self::NO_SUBJECT]);
            }

            return [
                'events' => $this->sortOptions($events),
                'causers' => $this->sortOptions($causers),
                'subjects' => $this->sortOptions($subjects),
            ];
        });
    }

    /**
     * @param  array<int, array{label: string, value: string}>  $options
     * @return array<int, array{label: string, value: string}>
     */
    private function sortOptions(array $options): array
    {
        $special = array_values(array_filter($options, fn (array $option): bool => str_starts_with($option['value'], '__')));
        $regular = array_values(array_filter($options, fn (array $option): bool => ! str_starts_with($option['value'], '__')));

        return [...$special, ...collect($regular)->unique('value')->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE)->values()->all()];
    }

    /**
     * @return Builder<Activity>
     */
    public function build(ActivityLogFiltersData $filters): Builder
    {
        $query = Activity::query()
            ->with(['causer', 'subject'])
            ->when($filters->search, function (Builder $query, string $search): void {
                $term = "%{$search}%";

                $query->where(function (Builder $query) use ($term): void {
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
                });
            })
            ->when($filters->events !== [], function (Builder $query) use ($filters): void {
                $query->where(function (Builder $query) use ($filters): void {
                    $events = array_values(array_diff($filters->events, [self::UNSPECIFIED_EVENT]));
                    $query->whereIn('event', $events);

                    if (in_array(self::UNSPECIFIED_EVENT, $filters->events, true)) {
                        $query->orWhereNull('event');
                    }
                });
            })
            ->when($filters->causers !== [], function (Builder $query) use ($filters): void {
                $query->where(function (Builder $query) use ($filters): void {
                    $causers = array_values(array_diff($filters->causers, [self::SYSTEM_CAUSER]));
                    $query->whereIn('causer_id', $causers)->where('causer_type', User::class);

                    if (in_array(self::SYSTEM_CAUSER, $filters->causers, true)) {
                        $query->orWhereNull('causer_id');
                    }
                });
            })
            ->when($filters->subjects !== [], function (Builder $query) use ($filters): void {
                $query->where(function (Builder $query) use ($filters): void {
                    $subjects = array_values(array_diff($filters->subjects, [self::NO_SUBJECT]));
                    $query->whereIn('subject_type', $subjects);

                    if (in_array(self::NO_SUBJECT, $filters->subjects, true)) {
                        $query->orWhereNull('subject_type');
                    }
                });
            })
            ->latest();

        if ($filters->from !== null) {
            $query->where('created_at', '>=', $this->date($filters->from)->startOfDay());
        }

        if ($filters->to !== null) {
            $query->where('created_at', '<=', $this->date($filters->to)->endOfDay());
        }

        return $query;
    }

    private function date(string $value): Carbon
    {
        if (! Carbon::hasFormat($value, 'Y-m-d')) {
            throw new UnexpectedValueException("Invalid activity log date `{$value}`.");
        }

        return Carbon::parse($value);
    }
}
