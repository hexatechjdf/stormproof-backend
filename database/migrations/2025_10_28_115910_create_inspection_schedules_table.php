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
        Schema::create('inspection_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_id')->constrained('inspections')->onDelete('cascade');
            $table->dateTime('preferred_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspection_schedules');
    }
};
