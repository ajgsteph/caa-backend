<?php

namespace App\Actions\Authentication;

use App\Models\User;

class UpdateEmailAction
{
    public function execute(User $user, string $newEmail): User
    {
        $user->update([
            'email' => $newEmail,
            'email_verified_at' => null,
        ]);

        return $user->fresh();
    }
}
