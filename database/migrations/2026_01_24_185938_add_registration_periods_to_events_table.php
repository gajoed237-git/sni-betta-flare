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
            $table->bigInteger('early_bird_fee')->nullable()->after('registration_fee');
            $table->date('early_bird_date')->nullable()->after('early_bird_fee');
            $table->date('normal_date')->nullable()->after('early_bird_date');
            $table->bigInteger('ots_fee')->nullable()->after('normal_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['early_bird_fee', 'early_bird_date', 'normal_date', 'ots_fee']);
        });
    }
};
