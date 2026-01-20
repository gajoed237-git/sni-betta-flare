<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('score_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fish_id')->unique()->constrained('fishes')->onDelete('cascade');
            $table->float('average_score')->default(0);
            $table->integer('total_judges')->default(0);
            $table->integer('rank_in_class')->nullable();
            $table->boolean('is_gc')->default(false);
            $table->boolean('is_bob')->default(false);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('score_snapshots'); }
};