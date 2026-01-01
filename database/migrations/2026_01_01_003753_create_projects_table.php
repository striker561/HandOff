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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->char('unique_id', 36)->unique();
            $table->char('client_unique_id', 36);

            $table->string('name', 255);
            $table->text('description')->nullable();

            $table->decimal('budget', 10, 2)->nullable();
            $table->string('currency', 3)->default('USD');

            $table->string('color', 7)->nullable();
            $table->integer('progress_percentage')->default(0);

            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            $table->json('metadata')->nullable();
            $table->string('status', 20)->default('active')->index();
            
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();

            //relations
            $table->foreign('unique_id')->references('unique_id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
