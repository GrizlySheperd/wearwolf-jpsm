<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('day_number')->nullable();
            $table->unsignedInteger('night_number')->nullable();
            $table->text('message');
            $table->string('visibility')->default('public'); // public or player:{id}
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_logs');
    }
};
