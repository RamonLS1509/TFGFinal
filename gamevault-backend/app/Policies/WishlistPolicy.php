<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Wishlist;

class WishlistPolicy
{
    public function update(User $user, Wishlist $wishlist): bool
    {
        return $user->id === $wishlist->user_id;
    }

    public function delete(User $user, Wishlist $wishlist): bool
    {
        return $user->id === $wishlist->user_id;
    }
}
