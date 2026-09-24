<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NightAction extends Model
{
    protected $fillable = [
        'game_id', 'night_number', 'actor_id', 'action', 'target_id',
    ];

    public function actor()
    {
        return $this->belongsTo(Player::class, 'actor_id');
    }

    public function target()
    {
        return $this->belongsTo(Player::class, 'target_id');
    }
}
