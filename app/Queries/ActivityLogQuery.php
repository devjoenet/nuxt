<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\User;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;

final class ActivityLogQuery
{
    private const string UNSPECIFIED_EVENT = '__unspecified__';

    private const string SYSTEM_CAUSER = '__system__';

    private const string NO_SUBJECT = '__no_subject__';

    /**
     * @return array{
     *     events: array<int, array{label: string, value: string}>,
     *     causers: array<int, array{label: string, value: string}>,
     *     subjects: array<int, array{label: string, value: string}>,
     * }
     */
    public function filterOptions(): array
    {
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
     * @param array{
     *     search: ?string,
     *     event: array<int, string>,
     *     causer: array<int, string>,
     *     subject: array<int, string>,
     *     from: ?string,
     *     to: ?string,
     * } $filters
     * @return Builder<Activity>
     */
    public function build(array $filters): Builder
    {
        $from = $this->parseDate($filters['from']);
        $to = $this->parseDate($filters['to']);

        $query = Activity::query()
            ->with(['causer', 'subject'])
            ->when($filters['search'], function (Builder $query, string $search): void {
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
            ->when($filters['event'] !== [], function (Builder $query) use ($filters): void {
                $query->where(function (Builder $query) use ($filters): void {
                    $events = array_values(array_diff($filters['event'], [self::UNSPECIFIED_EVENT]));
                    $query->whereIn('event', $events);

                    if (in_array(self::UNSPECIFIED_EVENT, $filters['event'], true)) {
                        $query->orWhereNull('event');
                    }
                });
            })
            ->when($filters['causer'] !== [], function (Builder $query) use ($filters): void {
                $query->where(function (Builder $query) use ($filters): void {
                    $causers = array_values(array_diff($filters['causer'], [self::SYSTEM_CAUSER]));
                    $query->whereIn('causer_id', $causers)->where('causer_type', User::class);

                    if (in_array(self::SYSTEM_CAUSER, $filters['causer'], true)) {
                        $query->orWhereNull('causer_id');
                    }
                });
            })
            ->when($filters['subject'] !== [], function (Builder $query) use ($filters): void {
                $query->where(function (Builder $query) use ($filters): void {
                    $subjects = array_values(array_diff($filters['subject'], [self::NO_SUBJECT]));
                    $query->whereIn('subject_type', $subjects);

                    if (in_array(self::NO_SUBJECT, $filters['subject'], true)) {
                        $query->orWhereNull('subject_type');
                    }
                });
            })
            ->latest();

        if ($from instanceof Carbon) {
            $query->where('created_at', '>=', $from->copy()->startOfDay());
        }

        if ($to instanceof Carbon) {
            $query->where('created_at', '<=', $to->copy()->endOfDay());
        }

        return $query;
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
