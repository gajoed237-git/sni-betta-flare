import os

def write_file(path, content):
    with open(path, 'w') as f:
        f.write(content)

master = """<?php

use Illuminate\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Schema\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('date');
            $table->string('location')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('divisions', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., A. HALFMOON
            $table->string('code')->unique(); // e.g., A
            $table->timestamps();
        });

        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('division_id')->constrained()->onDelete('cascade');
            $table->string('name'); // e.g., A1. HALFMOON BEBAS TERANG
         -¤table->string('code')->unique(); // e.g., A1
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classes');
        Schema::dropIfExists('divisions');
        Schema::dropIfExists('events');
    }
};
"""
write_file('database/migrations/2026_01_08_094613_create_competition_master_tables.php', master)
