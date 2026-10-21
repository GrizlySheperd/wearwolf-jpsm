<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MafiaRound extends Model
{
    protected $fillable = [
        'game_id', 'round_number', 'eliminated_player_id', 'eliminated_role', 'vote_snapshot',
    ];

    protected $casts = [
        'vote_snapshot' => 'array',
    ];

    public function eliminatedPlayer(): BelongsTo
    {
        return $this->belongsTo(MafiaPlayer::class, 'eliminated_player_id');
    }
}
