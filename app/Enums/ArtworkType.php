<?php

namespace App\Enums;

enum ArtworkType: string
{
    case PAINTING = 'PAINTING';
    case SCULPTURE = 'SCULPTURE';
    case PHOTOGRAPHY = 'PHOTOGRAPHY';
    case DRAWING = 'DRAWING';
    case ENGRAVING = 'ENGRAVING';
    case DIGITAL_ART = 'DIGITAL_ART';
    case TEXTILE = 'TEXTILE';
    case INSTALLATION = 'INSTALLATION';
    case OTHER = 'OTHER';

    public function label(): string
    {
        return match ($this) {
            self::PAINTING => 'Peinture',
            self::SCULPTURE => 'Sculpture',
            self::PHOTOGRAPHY => 'Photographie',
            self::DRAWING => 'Dessin',
            self::ENGRAVING => 'Gravure',
            self::DIGITAL_ART => 'Art numérique',
            self::TEXTILE => 'Textile',
            self::INSTALLATION => 'Installation',
            self::OTHER => 'Autre',
        };
    }
}
