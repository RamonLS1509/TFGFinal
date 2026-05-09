<?php

namespace App\Policies;

use App\Models\Library;
use App\Models\User;

//Gestiona el control de acceso a la biblioteca de cada usuario, garantizando que nadie pueda ver, modificar ni eliminar juegos que no sean suyas.
class LibraryPolicy
{
    public function view(User $user, Library $library): bool
    {
        return $user->id === $library->user_id;
    }

    public function update(User $user, Library $library): bool
    {
        return $user->id === $library->user_id;
    }

    public function delete(User $user, Library $library): bool
    {
        return $user->id === $library->user_id;
    }
}
