<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Child tables (foreign keys) first, then the tables they reference.
        Schema::dropIfExists('game_logs');
        Schema::dropIfExists('night_actions');
        Schema::dropIfExists('votes');
        Schema::dropIfExists('players');
        Schema::dropIfExists('games');
    }

    public function down(): void
    {
        // Irreversible: the role-based werewolf system has been replaced.
    }
};
