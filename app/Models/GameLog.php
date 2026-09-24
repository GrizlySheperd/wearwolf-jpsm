<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameLog extends Model
{
    protected $fillable = [
        'game_id', 'day_number', 'night_number', 'message', 'visibility',
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}
