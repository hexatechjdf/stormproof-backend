<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('photo_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('home_id')->constrained()->onDelete('cascade');
            $table->foreignId('inspection_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title')->nullable();
            $table->enum('type', ['pre_storm', 'post_storm', 'other'])->default('other');
            $table->text('description')->nullable();
            $table->string('pdf_path')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->boolean('companycam_synced')->default(false);
            $table->json('companycam_meta')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photo_reports');
    }
};
