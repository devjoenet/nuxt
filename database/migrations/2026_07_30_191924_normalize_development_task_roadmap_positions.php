<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('development_tasks')
            ->where(function (Builder $query): void {
                $query
                    ->where('status', '!=', 'planned')
                    ->orWhereNotNull('deleted_at');
            })
            ->update(['roadmap_position' => null]);

        DB::table('development_tasks')
            ->where('status', 'planned')
            ->whereNull('deleted_at')
            ->orderByRaw('roadmap_position is null')
            ->orderBy('roadmap_position')
            ->orderBy('planned_at')
            ->orderBy('id')
            ->pluck('id')
            ->each(function (int $taskId, int $index): void {
                DB::table('development_tasks')
                    ->where('id', $taskId)
                    ->update(['roadmap_position' => $index + 1]);
            });
    }
};
