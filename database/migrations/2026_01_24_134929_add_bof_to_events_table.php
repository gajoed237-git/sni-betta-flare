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
            // Add BOF
            $table->integer('point_bof')->nullable()->default(0)->after('point_bob');
            $table->string('label_bof')->nullable()->default('BEST OF FORM')->after('label_bob');

            // Change existing to nullable to support "optional" request
            $table->integer('point_gc')->nullable()->change();
            $table->integer('point_bob')->nullable()->change();
            $table->integer('point_bod')->nullable()->change();
            $table->integer('point_boo')->nullable()->change();
            $table->integer('point_bov')->nullable()->change();
            $table->integer('point_bos')->nullable()->change();

            $table->string('label_gc')->nullable()->change();
            $table->string('label_bob')->nullable()->change();
            $table->string('label_bod')->nullable()->change();
            $table->string('label_boo')->nullable()->change();
            $table->string('label_bov')->nullable()->change();
            $table->string('label_bos')->nullable()->change();
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
