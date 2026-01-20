<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('fishes', function (Blueprint $table) {
            $table->foreignId('original_class_id')->nullable()->constrained('betta_classes')->nullOnDelete();
            $table->text('admin_note')->nullable()->comment('Reason for DQ or Moving');
        });
    }

    public function down(): void
    {
        Schema::table('fishes', function (Blueprint $table) {
            $table->dropForeign(['original_class_id']);
            $table->dropColumn(['original_class_id', 'admin_note']);
        });
    }
};
