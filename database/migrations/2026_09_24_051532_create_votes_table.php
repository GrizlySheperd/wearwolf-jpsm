<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('day_number');
            $table->foreignId('voter_id')->constrained('players')->cascadeOnDelete();
            $table->foreignId('target_id')->nullable()->constrained('players')->nullOnDelete();
            $table->timestamps();

            $table->unique(['game_id', 'day_number', 'voter_id'], 'vote_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
