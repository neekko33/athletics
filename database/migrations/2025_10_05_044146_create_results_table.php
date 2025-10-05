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
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lane_id')->constrained()->onDelete('cascade');
            $table->foreignId('athlete_id')->constrained()->onDelete('cascade');
            $table->decimal('result_value', 10, 2)->nullable();
            $table->integer('rank')->nullable();
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('rank');
            $table->unique(['lane_id', 'athlete_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};
