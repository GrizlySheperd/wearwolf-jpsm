<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    protected $fillable = [
        'game_id', 'day_number', 'voter_id', 'target_id',
    ];

    public function voter()
    {
        return $this->belongsTo(Player::class, 'voter_id');
    }

    public function target()
    {
        return $this->belongsTo(Player::class, 'target_id');
    }
}
