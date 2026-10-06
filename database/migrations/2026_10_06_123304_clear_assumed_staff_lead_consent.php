<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('leads')
            ->whereNotNull('consented_at')
            ->whereColumn('consented_at', 'created_at')
            ->whereNull('intake_snapshot')
            ->whereExists(function (Builder $query): void {
                $query->selectRaw('1')->from('crm_activities')
                    ->whereColumn('crm_activities.lead_id', 'leads.id')
                    ->where('type', 'lead_created')
                    ->whereNotNull('user_id');
            })
            ->whereNotExists(function (Builder $query): void {
                $query->selectRaw('1')->from('crm_activities')
                    ->whereColumn('crm_activities.lead_id', 'leads.id')
                    ->where('type', 'public_intake_created');
            })
            ->update(['consented_at' => null]);
    }

    /** Corrected consent cannot be reconstructed; rollback must not fabricate it. */
    public function down(): void {}
};
