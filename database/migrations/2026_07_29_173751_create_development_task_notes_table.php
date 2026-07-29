<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('development_task_notes', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('development_task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('audience')->default('participants');
            $table->text('body');
            $table->timestamps();

            $table->index(
                ['development_task_id', 'audience', 'created_at'],
                'development_notes_task_audience_created_index',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('development_task_notes');
    }
};
