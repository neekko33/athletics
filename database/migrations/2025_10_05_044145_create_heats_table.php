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
        Schema::create('heats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_event_id')->constrained()->onDelete('cascade');
            $table->foreignId('grade_id')->nullable()->constrained()->onDelete('cascade');
            $table->integer('heat_number');
            $table->integer('total_lanes')->default(8);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('heats');
    }
};
