<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use \Illuminate\Database\Eloquent\Relations\HasMany;
use \Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    public function owners(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'libraries')
                    ->withPivot('purchased_at', 'price_paid', 'hours_played')
                    ->withTimestamps();
    }

    public function wishedBy(): BelongsToMany
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

    public function reviews(): HasMany
{
    return $this->hasMany(Review::class);
}

public function averageScore(): float
{
    return round($this->reviews()->avg('score') ?? 0, 1);
}
}
