<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('fish_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fish_id')->constrained('fishes')->onDelete('cascade');
            $table->foreignId('judge_id')->constrained('users')->onDelete('cascade');
            $table->integer('minus_kepala')->default(0);
            $table->integer('minus_badan')->default(0);
            $table->integer('minus_dorsal')->default(0);
            $table->integer('minus_anal')->default(0);
            $table->integer('minus_ekor')->default(0);
            $table->integer('minus_dasi')->default(0);
            $table->integer('minus_kerapian')->default(0);
            $table->integer('minus_warna')->default(0);
            $table->integer('minus_lain_lain')->default(0);
            $table->integer('total_minus')->default(0);
            $table->integer('total_score')->default(100);
            $table->text('admin_note')->nullable();
            $table->boolean('is_corrected')->default(false);
            $table->timestamps();
            $table->unique(['fish_id', 'judge_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('fish_scores'); }
};