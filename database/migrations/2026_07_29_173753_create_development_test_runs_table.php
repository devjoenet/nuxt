<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('development_test_runs', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('development_task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tester_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('requested_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('instructions');
            $table->string('build_reference', 2048)->nullable();
            $table->string('status')->default('pending');
            $table->text('feedback')->nullable();
            $table->timestamp('requested_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['development_task_id', 'status']);
            $table->index(['tester_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('development_test_runs');
    }
};
