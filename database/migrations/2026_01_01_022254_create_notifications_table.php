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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->uuid('unique_id')->unique();
            $table->uuid('user_unique_id')->index();

            $table->string('type', 255);
            $table->string('notifiable_type', 255);
            $table->uuid('notifiable_id');

            $table->json('data');
            $table->timestamp('read_at')->nullable()->index();

            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();

            $table->index(['notifiable_type', 'notifiable_id']);
            $table->foreign('user_unique_id')->references('unique_id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
