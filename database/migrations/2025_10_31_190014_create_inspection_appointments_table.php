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
        Schema::create('inspection_appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_id')->constrained('inspections')->onDelete('cascade');
            $table->foreignId('homeowner_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('inspector_id')->nullable()->constrained('users')->onDelete('set null');

            // Appointment Details
            $table->dateTime('preferred_datetime');
            $table->dateTime('confirmed_datetime')->nullable();
            $table->dateTime('completed_datetime')->nullable();

            // Inspection Information
            $table->enum('inspection_type', [
                'general',
                'pest',
                'mold',
                'radon',
                'termite',
                'roof',
                'foundation',
                'electrical',
                'plumbing'
            ])->default('general');

            // Contact Information
            $table->enum('contact_method', ['phone', 'email', 'sms', 'whatsapp'])->default('email');
            $table->string('contact_number')->nullable();
            $table->string('contact_email')->nullable();

            // Notes and Instructions
            $table->text('notes')->nullable();
            $table->text('access_instructions')->nullable();
            $table->text('inspector_notes')->nullable();

            // Status
            $table->enum('status', [
                'pending',      // Awaiting inspector confirmation
                'confirmed',    // Inspector confirmed the appointment
                'completed',    // Appointment completed
                'cancelled',    // Appointment cancelled
                'no_show'       // Homeowner didn't show up
            ])->default('pending');

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes for better query performance
            $table->index('inspection_id');
            $table->index('homeowner_id');
            $table->index('inspector_id');
            $table->index('status');
            $table->index('preferred_datetime');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspection_appointments');
    }
};