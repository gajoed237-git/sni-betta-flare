<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fishes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_id')->constrained('betta_classes')->onDelete('cascade');
            $table->string('registration_no')->unique();
            $table->string('participant_name');
            $table->string('team_name')->nullable();
            $table->string('phone')->nullable();
            $table->enum('status', ['registered', 'checking', 'judging', 'completed', 'disqualified', 'moved'])->default('registered');
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('fishes');
    }
};
