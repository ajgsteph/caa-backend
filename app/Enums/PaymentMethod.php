<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case ORANGE_MONEY = 'ORANGE_MONEY';
    case MTN_MOMO = 'MTN_MOMO';
    case WAVE = 'WAVE';
    case BANK_CARD = 'BANK_CARD';

    public function label(): string
    {
        return match ($this) {
            self::ORANGE_MONEY => 'Orange Money',
            self::MTN_MOMO => 'MTN Mobile Money',
            self::WAVE => 'Wave',
            self::BANK_CARD => 'Carte bancaire',
        };
    }
}
