<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MafiaGame extends Model
{
    protected $fillable = [
        'status', 'mafia_total', 'citizen_total', 'round_number', 'winner',
    ];

    public function players(): HasMany
    {
        return $this->hasMany(MafiaPlayer::class, 'game_id');
    }

    public function rounds(): HasMany
    {
        return $this->hasMany(MafiaRound::class, 'game_id')->orderByDesc('round_number');
    }

    public function alivePlayers()
    {
        return $this->players->where('is_eliminated', false)->sortByDesc('votes')->values();
    }

    public function eliminatedPlayers()
    {
        return $this->players->where('is_eliminated', true)->sortByDesc('eliminated_round')->values();
    }

    public function mafiaRemaining(): int
    {
        return $this->mafia_total - $this->players->where('is_eliminated', true)->where('revealed_role', 'mafia')->count();
    }

    public function citizensRemaining(): int
    {
        return $this->citizen_total - $this->players->where('is_eliminated', true)->where('revealed_role', 'citizen')->count();
    }

    public function checkWinner(): ?string
    {
        $mafiaLeft = $this->mafiaRemaining();
        $citizensLeft = $this->citizensRemaining();

        if ($mafiaLeft <= 0) {
            return 'citizens';
        }

        if ($mafiaLeft >= $citizensLeft) {
            return 'mafia';
        }

        return null;
    }
}
