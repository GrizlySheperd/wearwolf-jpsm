<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'status', 'day_number', 'night_number', 'winner',
    ];

    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    public function nightActions(): HasMany
    {
        return $this->hasMany(NightAction::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(GameLog::class);
    }

    public function alivePlayers()
    {
        return $this->players->where('is_alive', true);
    }

    public function aliveWerewolves()
    {
        return $this->alivePlayers()->where('role', 'werewolf');
    }

    public function aliveNonWerewolves()
    {
        return $this->alivePlayers()->where('role', '!=', 'werewolf');
    }

    public function currentNightActions()
    {
        return $this->nightActions()->where('night_number', $this->night_number)->get();
    }

    public function currentVotes()
    {
        return $this->votes()->where('day_number', $this->day_number)->get();
    }

    public function publicLogsFor(?Player $player)
    {
        return $this->logs()
            ->where(function ($q) use ($player) {
                $q->where('visibility', 'public');
                if ($player) {
                    $q->orWhere('visibility', 'player:'.$player->id);
                }
            })
            ->orderByDesc('id')
            ->get();
    }

    public function addLog(string $message, string $visibility = 'public'): void
    {
        $this->logs()->create([
            'day_number' => $this->status === 'day' ? $this->day_number : null,
            'night_number' => $this->status === 'night' ? $this->night_number : null,
            'message' => $message,
            'visibility' => $visibility,
        ]);
    }

    public function stateToken(): string
    {
        return implode('|', [
            $this->status,
            $this->day_number,
            $this->night_number,
            $this->winner,
            $this->players()->count(),
            $this->players()->where('is_alive', true)->count(),
            $this->nightActions()->where('night_number', $this->night_number)->count(),
            $this->votes()->where('day_number', $this->day_number)->count(),
            $this->updated_at?->timestamp,
        ]);
    }
}
