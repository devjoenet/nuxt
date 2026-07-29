<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('development_task_links', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('development_task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('added_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type');
            $table->string('label')->nullable();
            $table->string('url', 2048);
            $table->timestamps();

            $table->index(['development_task_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('development_task_links');
    }
};
