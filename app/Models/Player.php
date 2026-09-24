<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Player extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id', 'name', 'token', 'role', 'is_alive', 'is_host', 'death_reason',
    ];

    protected $casts = [
        'is_alive' => 'boolean',
        'is_host' => 'boolean',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function isWerewolf(): bool
    {
        return $this->role === 'werewolf';
    }
}
