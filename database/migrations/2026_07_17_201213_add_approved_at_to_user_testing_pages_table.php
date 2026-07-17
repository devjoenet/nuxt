<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_testing_pages', function (Blueprint $table): void {
            $table->timestamp('approved_at')->nullable()->after('tested_at')->index();
        });
    }

    public function down(): void
    {
        Schema::table('user_testing_pages', function (Blueprint $table): void {
            $table->dropColumn('approved_at');
        });
    }
};
