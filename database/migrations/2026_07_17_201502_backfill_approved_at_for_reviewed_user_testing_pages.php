<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('user_testing_pages')
            ->whereNotNull('tested_at')
            ->whereNull('approved_at')
            ->update(['approved_at' => DB::raw('tested_at')]);
    }

    public function down(): void
    {
        // Existing approvals cannot be distinguished from approvals made after this backfill.
    }
};
