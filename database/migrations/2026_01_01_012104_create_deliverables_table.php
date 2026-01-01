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
        Schema::create('deliverables', function (Blueprint $table) {
            $table->id();
            $table->char('unique_id', 36)->unique();
            $table->char('project_unique_id', 36)->index();
            $table->char('milestone_unique_id', 36)->nullable()->index();
            $table->char('created_by_unique_id', 36)->index();

            $table->string('name', 255);
            $table->text('description')->nullable();

            $table->string('type', 20)->default('file')->index();
            $table->string('status', 20)->default('draft')->index();

            $table->string('version', 50)->default('1.0');
            $table->integer('order')->default(0);

            $table->date('due_date')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->char('approved_by_unique_id', 36)->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();

            //relations
            $table->foreign('project_unique_id')->references('unique_id')->on('projects')->cascadeOnDelete();
            $table->foreign('milestone_unique_id')->references('unique_id')->on('milestones')->nullOnDelete();
            $table->foreign('created_by_unique_id')->references('unique_id')->on('users')->cascadeOnDelete();
            $table->foreign('approved_by_unique_id')->references('unique_id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliverables');
    }
};
