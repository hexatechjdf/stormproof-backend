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
        Schema::create('inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('homeowner_id')->constrained('users');
            $table->foreignId('agency_id')->constrained('agencies');
            $table->foreignId('assigned_advisor_id')->nullable()->constrained('users');

            $table->string('status'); // e.g., 'pending_schedule', 'pending_approval', 'scheduled', 'in_progress', 'review_pending', 'completed', 'cancelled'
            $table->string('trigger_type'); // e.g., 'initial_subscription', 'annual', 'storm_alert'

            $table->text('property_address'); // To be fetched from CRM or entered manually
            $table->string('zip_code'); // Crucial for finding nearby advisors

            $table->text('admin_notes')->nullable();
            $table->text('advisor_notes')->nullable();

            $table->timestamp('scheduled_at')->nullable(); // The final confirmed date/time
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspections');
    }
};
