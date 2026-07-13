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
            $table->timestamp('client_since_at')->nullable()->index()->after('website');
            $table->timestamp('archived_at')->nullable()->index()->after('client_since_at');
            $table->softDeletes();
        });

        Schema::table('contacts', function (Blueprint $table): void {
            $table->timestamp('archived_at')->nullable()->index()->after('phone');
            $table->softDeletes();
        });

        Schema::table('leads', function (Blueprint $table): void {
            $table->timestamp('archived_at')->nullable()->index()->after('consented_at');
            $table->timestamp('converted_at')->nullable()->index()->after('archived_at');
            $table->softDeletes();
            $table->index(['owner_id', 'status']);
            $table->index(['created_at', 'status']);
        });

        Schema::table('follow_up_tasks', function (Blueprint $table): void {
            $table->string('priority')->default('normal')->index()->after('status');
            $table->softDeletes();
        });

        Schema::create('notes', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->morphs('subject');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notes');

        Schema::table('follow_up_tasks', function (Blueprint $table): void {
            $table->dropSoftDeletes();
            $table->dropColumn('priority');
        });

        Schema::table('leads', function (Blueprint $table): void {
            $table->dropIndex(['owner_id', 'status']);
            $table->dropIndex(['created_at', 'status']);
            $table->dropSoftDeletes();
            $table->dropColumn(['archived_at', 'converted_at']);
        });

        Schema::table('contacts', function (Blueprint $table): void {
            $table->dropSoftDeletes();
            $table->dropColumn('archived_at');
        });

        Schema::table('organizations', function (Blueprint $table): void {
            $table->dropSoftDeletes();
            $table->dropColumn(['client_since_at', 'archived_at']);
        });
    }
};
