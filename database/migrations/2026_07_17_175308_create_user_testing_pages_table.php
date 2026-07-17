<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_testing_pages', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->string('category')->index();
            $table->string('title');
            $table->string('route_name')->nullable();
            $table->string('path');
            $table->unsignedInteger('sort_order')->index();
            $table->foreignId('tested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('tested_at')->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_testing_pages');
    }
};
