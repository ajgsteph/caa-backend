<?php

namespace App\Actions\Authentication;

use App\Models\User;

class UpdatePhoneAction
{
    public function execute(User $user, string $phone): User
    {
        $user->update(['phone' => $phone]);

        return $user->fresh();
    }
}
