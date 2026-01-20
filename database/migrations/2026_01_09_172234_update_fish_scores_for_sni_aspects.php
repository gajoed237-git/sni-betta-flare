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
        Schema::table('fish_scores', function (Blueprint $table) {
            // Additional aspects for SNI
            $table->string('kedokan_notes')->nullable();
            $table->string('mental_notes')->nullable();
            $table->string('proporsi_notes')->nullable();

            // To store descriptions for existing integer categories if standard is SNI
            $table->string('kepala_notes')->nullable();
            $table->string('badan_notes')->nullable();
            $table->string('dorsal_notes')->nullable();
            $table->string('anal_notes')->nullable();
            $table->string('ekor_notes')->nullable();
            $table->string('dasi_notes')->nullable();
            $table->string('warna_notes')->nullable();
            $table->string('kerapihan_notes')->nullable();

            // Final Rank assigned by judge (for SNI events)
            $table->integer('final_rank')->nullable();
        });

        Schema::table('fishes', function (Blueprint $table) {
            $table->integer('final_rank')->nullable();
            $table->string('winner_type')->nullable(); // 1, 2, 3, GC, BoS
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fish_scores', function (Blueprint $table) {
            $table->dropColumn([
                'kedokan_notes',
                'mental_notes',
                'proporsi_notes',
                'kepala_notes',
                'badan_notes',
                'dorsal_notes',
                'anal_notes',
                'ekor_notes',
                'dasi_notes',
                'warna_notes',
                'kerapihan_notes',
                'final_rank'
            ]);
        });

        Schema::table('fishes', function (Blueprint $table) {
            $table->dropColumn(['final_rank', 'winner_type']);
        });
    }
};
