<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Artwork;
use App\Models\User;

class ArtworkPolicy
{
    public function view(User $user, Artwork $artwork): bool
    {
        if ($user->hasAnyRole([UserRole::SUPER_ADMIN->value, UserRole::ADMIN->value])) {
            return true;
        }

        return $artwork->artist_id === $user->id;
    }

    public function update(User $user, Artwork $artwork): bool
    {
        return $artwork->artist_id === $user->id;
    }

    public function delete(User $user, Artwork $artwork): bool
    {
        return $artwork->artist_id === $user->id;
    }
}
