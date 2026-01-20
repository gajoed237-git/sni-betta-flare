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
            $table->renameColumn('minus_kerapian', 'minus_kerapihan');
        });
    }

    public function down(): void
    {
        Schema::table('fish_scores', function (Blueprint $table) {
            $table->renameColumn('minus_kerapihan', 'minus_kerapian');
        });
    }
};
