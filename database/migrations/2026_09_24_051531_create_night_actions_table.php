<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('night_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('night_number');
            $table->foreignId('actor_id')->constrained('players')->cascadeOnDelete();
            $table->string('action'); // kill, save, inspect
            $table->foreignId('target_id')->nullable()->constrained('players')->nullOnDelete();
            $table->timestamps();

            $table->unique(['game_id', 'night_number', 'actor_id', 'action'], 'night_action_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('night_actions');
    }
};
