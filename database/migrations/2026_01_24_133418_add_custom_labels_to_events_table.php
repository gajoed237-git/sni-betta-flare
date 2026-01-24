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
            $table->string('label_gc')->default('GRAND CHAMPION')->after('point_gc');
            $table->string('label_bob')->default('BEST OF BEST')->after('point_bob');
            $table->string('label_bod')->default('BEST OF DIVISION')->after('point_bod');
            $table->string('label_boo')->default('BEST OF OTHER')->after('point_boo');
            $table->string('label_bov')->default('BEST OF VARIETY')->after('point_bov');
            $table->string('label_bos')->default('BEST OF SHOW')->after('point_bos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            //
        });
    }
};
