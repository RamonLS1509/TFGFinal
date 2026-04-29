<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'developer',
        'publisher',
        'price',
        'cover_image',
        'header_image',
        'screenshots',
        'genres',
        'platforms',
        'release_date',
        'is_active',
        'metacritic_score',
    ];

    protected function casts(): array
    {
        return [
            'screenshots' => 'array',
            'genres'      => 'array',
            'platforms'   => 'array',
            'release_date' => 'date',
            'is_active'   => 'boolean',
            'price'       => 'decimal:2',
        ];
    }

    // Auto-genera el slug desde el título
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Game $game) {
            if (empty($game->slug)) {
                $game->slug = Str::slug($game->title);
            }
        });
    }

    public function owners(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'libraries')
                    ->withPivot('purchased_at', 'price_paid', 'hours_played')
                    ->withTimestamps();
    }

    public function wishedBy(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'wishlists')
                    ->withPivot('priority')
                    ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByGenre($query, string $genre)
    {
        return $query->whereJsonContains('genres', $genre);
    }

    public function reviews(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(Review::class);
}

public function averageScore(): float
{
    return round($this->reviews()->avg('score') ?? 0, 1);
}
}
