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
        Schema::create('claim_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('home_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->enum('doc_type', ['policy_declaration', 'full_policy', 'wind_mitigation', 'inspection', 'other'])->default('other');
            $table->string('file_path');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('date_of_document')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claim_documents');
    }
};
