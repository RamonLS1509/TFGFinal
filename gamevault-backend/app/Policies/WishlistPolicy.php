<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Wishlist;

//Gestiona el control de acceso a la wishlist de cada usuario, garantizando que nadie pueda modificar ni eliminar registros que no sean suyos.
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
