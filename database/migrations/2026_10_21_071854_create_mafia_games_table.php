<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mafia_games', function (Blueprint $table) {
            $table->id();
            $table->string('status')->default('setup'); // setup, active, ended
            $table->unsignedInteger('mafia_total');
            $table->unsignedInteger('citizen_total');
            $table->unsignedInteger('round_number')->default(0);
            $table->string('winner')->nullable(); // mafia, citizens
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mafia_games');
    }
};
