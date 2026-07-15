<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table): void {
            $table->foreignId('owner_id')->nullable()->after('website')->constrained('users')->nullOnDelete();
            $table->index(['owner_id', 'archived_at']);
        });

        Schema::table('contacts', function (Blueprint $table): void {
            $table->foreignId('owner_id')->nullable()->after('phone')->constrained('users')->nullOnDelete();
            $table->index(['owner_id', 'archived_at']);
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table): void {
            $table->dropIndex(['owner_id', 'archived_at']);
            $table->dropConstrainedForeignId('owner_id');
        });

        Schema::table('contacts', function (Blueprint $table): void {
            $table->dropIndex(['owner_id', 'archived_at']);
            $table->dropConstrainedForeignId('owner_id');
        });
    }
};
