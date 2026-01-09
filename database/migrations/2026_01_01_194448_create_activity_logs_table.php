<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_unique_id')->nullable()->index();

            $table->string('log_name', 255)->nullable();
            $table->text('description');

            $table->string('subject_type', 255)->nullable();
            $table->uuid('subject_id')->nullable();

            $table->string('causer_type', 255)->nullable();
            $table->uuid('causer_id')->nullable();

            $table->json('properties')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            $table->index(['subject_type', 'subject_id']);
            $table->index(['causer_type', 'causer_id']);
            $table->index('created_at');
            $table->foreign('user_unique_id')->references('unique_id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
