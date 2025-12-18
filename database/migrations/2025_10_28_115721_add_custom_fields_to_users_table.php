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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('agency_id')->nullable()->constrained('agencies')->after('id');

            // Role of the user within the platform
            $table->string('role')->default('homeowner')->after('email'); // superadmin, admin, homeowner, advisor, partner

            // CRM mapping fields
            $table->string('crm_location_id')->nullable()->after('role');
            $table->string('crm_user_id')->nullable()->after('crm_location_id');

            // Add an index for faster lookups on CRM IDs
            $table->index(['crm_location_id', 'crm_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
