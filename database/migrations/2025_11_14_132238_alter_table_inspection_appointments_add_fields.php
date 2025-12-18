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
        Schema::table('inspection_appointments', function (Blueprint $table) {
            $table->string('crm_appointment_id')->nullable()->after('status');
            $table->string('crm_location_id')->nullable()->after('crm_appointment_id');
            $table->string('crm_calendar_id')->nullable()->after('crm_location_id');
            $table->string('crm_contact_id')->nullable()->after('crm_calendar_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inspection_appointments', function (Blueprint $table) {
            $table->dropColumn(['crm_appointment_id', 'crm_location_id', 'crm_calendar_id', 'crm_contact_id']);
        });
    }
};
