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
            // Winner Points
            $table->integer('point_rank1')->default(15);
            $table->integer('point_rank2')->default(7);
            $table->integer('point_rank3')->default(3);
            $table->integer('point_gc')->default(30);
            $table->integer('point_bob')->default(50);

            // IBC Faults (Minus points)
            $table->integer('ibc_minus_ringan')->default(3);
            $table->integer('ibc_minus_kecil')->default(5);
            $table->integer('ibc_minus_besar')->default(9);
            $table->integer('ibc_minus_berat')->default(17);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'point_rank1',
                'point_rank2',
                'point_rank3',
                'point_gc',
                'point_bob',
                'ibc_minus_ringan',
                'ibc_minus_kecil',
                'ibc_minus_besar',
                'ibc_minus_berat'
            ]);
        });
    }
};
