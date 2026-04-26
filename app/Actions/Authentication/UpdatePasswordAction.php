<?php

namespace App\Actions\Authentication;

use App\Models\User;

class UpdatePasswordAction
{
    public function execute(User $user, string $newPassword): User
    {
        $user->update(['password' => $newPassword]);

        return $user->fresh();
    }
}
