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
        Schema::create('milestones', function (Blueprint $table) {
            $table->id();
            $table->char('unique_id', 36)->unique();
            $table->char('project_unique_id', 36);

            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->integer('order')->default(0);

            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->integer('progress_percentage')->default(0);
            $table->string('status')->default('pending');

            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();

            //relations
            $table->foreign('project_unique_id')->references('unique_id')->on('projects');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('milestones');
    }
};
