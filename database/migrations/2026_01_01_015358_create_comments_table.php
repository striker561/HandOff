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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->char('unique_id', 36)->unique();

            $table->string('commentable_type', 255);
            $table->char('commentable_id', 36);

            $table->char('parent_unique_id', 36)->nullable()->index();
            $table->char('user_unique_id', 36)->index();

            $table->text('body');
            $table->boolean('is_internal')->default(false)->index();
            $table->json('mentioned_users')->nullable();
            $table->timestamp('read_at')->nullable();

            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();

            // Indexes
            $table->index(['commentable_type', 'commentable_id']);

            // Relations
            $table->foreign('parent_unique_id')->references('unique_id')->on('comments')->nullOnDelete();
            $table->foreign('user_unique_id')->references('unique_id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
