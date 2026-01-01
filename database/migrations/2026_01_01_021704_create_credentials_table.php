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
        Schema::create('credentials', function (Blueprint $table) {
            $table->id();
            $table->char('unique_id', 36)->unique();
            $table->char('project_unique_id', 36)->index();

            $table->string('name', 255);
            $table->string('type', 50)->default('login')->index();

            $table->text('username')->nullable();
            $table->text('password')->nullable();
            $table->string('url', 500)->nullable();
            $table->text('notes')->nullable();

            $table->json('metadata')->nullable();
            $table->timestamp('last_accessed_at')->nullable();

            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();

            // Relations
            $table->foreign('project_unique_id')->references('unique_id')->on('projects')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credentials');
    }
};
