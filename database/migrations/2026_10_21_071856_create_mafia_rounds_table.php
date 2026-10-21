<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mafia_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('mafia_games')->cascadeOnDelete();
            $table->unsignedInteger('round_number');
            $table->foreignId('eliminated_player_id')->nullable()->constrained('mafia_players')->nullOnDelete();
            $table->string('eliminated_role')->nullable(); // mafia, citizen
            $table->json('vote_snapshot')->nullable(); // {player_id: votes} at the moment of elimination
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mafia_rounds');
    }
};
