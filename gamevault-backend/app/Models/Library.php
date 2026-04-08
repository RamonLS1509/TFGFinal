<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Library extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'game_id',
        'purchased_at',
        'price_paid',
        'hours_played',
        'last_played_at',
    ];

    protected function casts(): array
    {
        return [
            'purchased_at'  => 'datetime',
            'last_played_at' => 'datetime',
            'price_paid'    => 'decimal:2',
        ];
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function game(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}
