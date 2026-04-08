<?php

namespace App\Policies;

use App\Models\Library;
use App\Models\User;

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
