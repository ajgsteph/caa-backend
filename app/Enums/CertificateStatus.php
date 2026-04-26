<?php

namespace App\Enums;

enum CertificateStatus: string
{
    case ACTIVE = 'ACTIVE';
    case REVOKED = 'REVOKED';
    case PENDING = 'PENDING';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Actif',
            self::REVOKED => 'Révoqué',
            self::PENDING => 'En attente',
        };
    }

    public function isValid(): bool
    {
        return $this === self::ACTIVE;
    }
}
