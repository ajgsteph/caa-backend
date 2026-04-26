<?php

namespace App\Enums;

enum AccountStatus: string
{
    case ACTIVE = 'ACTIVE';
    case SUSPENDED = 'SUSPENDED';
    case VERIFIED = 'VERIFIED';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Actif',
            self::SUSPENDED => 'Suspendu',
            self::VERIFIED => 'Vérifié',
        };
    }

    public function canLogin(): bool
    {
        return $this !== self::SUSPENDED;
    }
}
