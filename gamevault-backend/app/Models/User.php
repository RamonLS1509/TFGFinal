<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use \Illuminate\Database\Eloquent\Relations\HasMany;
use \Illuminate\Database\Eloquent\Relations\BelongsToMany;
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'avatar',
        'bio',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function library(): HasMany
    {
        return $this->hasMany(Library::class);
    }

    public function games():BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'libraries')
                    ->withPivot('purchased_at', 'price_paid', 'hours_played', 'last_played_at')
                    ->withTimestamps();
    }

    public function wishlist(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function wishedGames(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'wishlists')
                    ->withPivot('priority')
                    ->withTimestamps();
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function reviews(): HasMany
{
    return $this->hasMany(Review::class);
}
}
