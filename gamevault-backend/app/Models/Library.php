<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}
