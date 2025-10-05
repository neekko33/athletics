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
        Schema::create('lane_athletes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lane_id')->constrained()->onDelete('cascade');
            $table->foreignId('athlete_id')->constrained()->onDelete('cascade');
            $table->integer('relay_position')->nullable(); // 接力项目的棒次（1-4）
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lane_athletes');
    }
};
