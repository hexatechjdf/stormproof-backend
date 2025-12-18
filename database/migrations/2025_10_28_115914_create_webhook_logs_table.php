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
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('source'); // e.g., 'stripe', 'companycam'
            $table->string('event_type');
            $table->longText('payload');
            $table->string('status')->default('processed'); // 'processed', 'failed'
            $table->text('notes')->nullable(); // To log any errors during processing
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_logs');
    }
};
