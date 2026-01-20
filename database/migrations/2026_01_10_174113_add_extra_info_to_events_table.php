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
            $table->integer('ticket_price')->default(0)->after('registration_fee');
            $table->string('committee_name')->nullable()->after('ticket_price');
            $table->string('brochure_image')->nullable()->after('committee_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['ticket_price', 'committee_name', 'brochure_image']);
        });
    }
};
