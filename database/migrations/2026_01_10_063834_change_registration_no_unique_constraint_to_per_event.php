<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Change registration_no unique constraint from global to per-event scope.
     * This allows same registration numbers (e.g., 0001) across different events.
     */
    public function up(): void
    {
        Schema::table('fishes', function (Blueprint $table) {
            // Drop the existing global unique constraint
            $table->dropUnique(['registration_no']);

            // Add composite unique constraint: registration_no must be unique per event
            $table->unique(['event_id', 'registration_no'], 'fishes_event_registration_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fishes', function (Blueprint $table) {
            // Drop the composite unique constraint
            $table->dropUnique('fishes_event_registration_unique');

            // Restore the global unique constraint
            $table->unique('registration_no');
        });
    }
};
