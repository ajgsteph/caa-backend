<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = 'PENDING';
    case SUCCESSFUL = 'SUCCESSFUL';
    case FAILED = 'FAILED';
    case REFUNDED = 'REFUNDED';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'En attente',
            self::SUCCESSFUL => 'Réussi',
            self::FAILED => 'Échoué',
            self::REFUNDED => 'Remboursé',
        };
    }
}
