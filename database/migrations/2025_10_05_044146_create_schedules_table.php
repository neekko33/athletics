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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('heat_id')->constrained()->onDelete('cascade');
            $table->dateTime('scheduled_at');
            $table->dateTime('end_at')->nullable();
            $table->string('venue')->nullable();
            $table->integer('duration')->nullable(); // 分钟
            $table->string('status')->default('pending'); // pending, in_progress, completed, cancelled
            $table->text('notes')->nullable();
            $table->integer('display_order')->nullable();
            $table->timestamps();
            
            $table->index('scheduled_at');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
