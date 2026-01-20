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
        Schema::table('participants', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('event_id')->constrained()->onDelete('cascade');
            $table->string('payment_status')->default('unpaid')->after('notes'); // unpaid, pending, paid, rejected
            $table->string('payment_proof')->nullable()->after('payment_status');
            $table->integer('total_fee')->default(0)->after('payment_proof');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'payment_status', 'payment_proof', 'total_fee']);
        });
    }
};
