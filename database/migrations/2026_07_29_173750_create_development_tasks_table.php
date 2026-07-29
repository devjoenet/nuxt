<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('development_tasks', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('title');
            $table->string('request_type');
            $table->string('affected_area');
            $table->text('description');
            $table->text('desired_outcome');
            $table->text('acceptance_criteria');
            $table->string('status')->default('submitted');
            $table->string('priority')->default('normal');
            $table->unsignedInteger('roadmap_position')->nullable();
            $table->foreignId('submitter_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at');
            $table->timestamp('information_requested_at')->nullable();
            $table->timestamp('review_started_at')->nullable();
            $table->timestamp('planned_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('blocked_at')->nullable();
            $table->timestamp('ready_for_testing_at')->nullable();
            $table->timestamp('changes_requested_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'created_at']);
            $table->index(['status', 'priority', 'roadmap_position']);
            $table->index(['submitter_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('development_tasks');
    }
};
