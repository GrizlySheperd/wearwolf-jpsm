<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MafiaPlayer extends Model
{
    protected $fillable = [
        'game_id', 'name', 'sort_order', 'votes', 'is_eliminated', 'eliminated_round', 'revealed_role',
    ];

    protected $casts = [
        'is_eliminated' => 'boolean',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(MafiaGame::class, 'game_id');
    }
}
