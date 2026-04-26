<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Certificate;
use App\Models\User;

class CertificatePolicy
{
    public function view(User $user, Certificate $certificate): bool
    {
        if ($user->hasAnyRole([UserRole::SUPER_ADMIN->value, UserRole::ADMIN->value])) {
            return true;
        }

        return $certificate->artist_id === $user->id;
    }

    public function downloadLink(User $user, Certificate $certificate): bool
    {
        return $this->view($user, $certificate);
    }

    public function revoke(User $user, Certificate $certificate): bool
    {
        return $user->hasAnyRole([UserRole::SUPER_ADMIN->value, UserRole::ADMIN->value]);
    }
}
