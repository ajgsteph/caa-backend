<?php

namespace App\Actions\Authentication;

use App\Models\User;

class LogoutAction
{
    public function execute(User $user, bool $allDevices = false): void
    {
        if ($allDevices) {
            $user->tokens()->delete();
            return;
        }

        $user->currentAccessToken()?->delete();
    }
}
