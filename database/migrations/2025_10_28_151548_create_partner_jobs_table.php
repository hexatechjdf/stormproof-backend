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
        Schema::create('partner_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_id')->constrained()->onDelete('cascade');
            $table->foreignId('agency_id')->constrained();
            $table->foreignId('partner_id')->constrained('users');

            $table->string('status')->default('assigned');
            $table->text('job_description');
            $table->decimal('invoice_amount', 10, 2)->nullable();
            $table->string('invoice_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_jobs');
    }
};
