<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mafia_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('mafia_games')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedInteger('votes')->default(0);
            $table->boolean('is_eliminated')->default(false);
            $table->unsignedInteger('eliminated_round')->nullable();
            $table->string('revealed_role')->nullable(); // mafia, citizen
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mafia_players');
    }
};
