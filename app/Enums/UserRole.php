<?php

namespace App\Enums;

enum UserRole: string
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN = 'admin';
    case ARTIST = 'artist';
    case GALLERY = 'gallery';

    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super administrateur',
            self::ADMIN => 'Administrateur',
            self::ARTIST => 'Artiste',
            self::GALLERY => 'Galerie',
        };
    }

    public static function values(): array
    {
        return array_map(fn (self $r) => $r->value, self::cases());
    }
}
