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
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->char('unique_id', 36)->unique();
            $table->char('project_unique_id', 36)->nullable()->index();
            $table->char('deliverable_unique_id', 36)->nullable()->index();
            $table->char('scheduled_by_unique_id', 36)->index();

            $table->string('title', 255);
            $table->text('description')->nullable();

            $table->timestamp('scheduled_at')->index();
            $table->integer('duration_minutes')->default(60);
            $table->string('location', 255)->nullable();

            $table->string('status', 50)->default('scheduled')->index();
            $table->text('meeting_notes')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();

            // Relations
            $table->foreign('project_unique_id')->references('unique_id')->on('projects')->nullOnDelete();
            $table->foreign('deliverable_unique_id')->references('unique_id')->on('deliverables')->nullOnDelete();
            $table->foreign('scheduled_by_unique_id')->references('unique_id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};
