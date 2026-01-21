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
        Schema::table('events', function (Blueprint $table) {
            // Advanced IBC Points
            $table->integer('point_bod')->default(30);
            $table->integer('point_boo')->default(45);
            $table->integer('point_bov')->default(40);
            $table->integer('point_bos')->default(60);

            // Calculation Mode
            $table->string('point_accumulation_mode')->default('highest'); // accumulation vs highest
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'point_bod',
                'point_boo',
                'point_bov',
                'point_bos',
                'point_accumulation_mode'
            ]);
        });
    }
};
