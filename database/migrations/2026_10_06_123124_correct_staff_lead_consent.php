<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table): void {
            $table->timestamp('consented_at')->nullable()
                ->comment('Time of affirmative consent recorded by public intake; null means no documented consent. Staff entry alone is not consent.')
                ->change();
        });

    }

    /**
     * Corrected consent cannot be reconstructed. Keep the nullable column on rollback
     * rather than fabricating consent timestamps or rejecting legitimate staff leads.
     */
    public function down(): void {}
};
