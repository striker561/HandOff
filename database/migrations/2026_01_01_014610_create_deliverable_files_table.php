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
        Schema::create('deliverable_files', function (Blueprint $table) {
            $table->id();
            $table->char('unique_id', 36)->unique();
            $table->char('deliverable_unique_id', 36);
            $table->char('uploaded_by_unique_id', 36);

            $table->string('filename', 255);
            $table->string('original_filename', 255);
            $table->string('file_path', 500);
            $table->bigInteger('file_size');
            $table->string('mime_type', 100);

            $table->string('version', 50)->default('1.0');
            $table->boolean('is_latest')->default(true);
            $table->integer('download_count')->default(0);

            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();

            //relations
            $table->foreign('deliverable_unique_id')->references('unique_id')->on('deliverables')->cascadeOnDelete();
            $table->foreign('uploaded_by_unique_id')->references('unique_id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliverable_files');
    }
};
