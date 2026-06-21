<?php

namespace App\Actions\Certificate;

use App\Models\Certificate;
use RuntimeException;

class GenerateUniqueNumberAction
{
    /** Alphabet sans caractères ambigus (pas de 0/O/1/I/L). */
    private const ALPHABET = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    private const CODE_LENGTH = 10;

    private const MAX_ATTEMPTS = 10;

    public function execute(): string
    {
        $year = (int) date('Y');

        for ($attempt = 0; $attempt < self::MAX_ATTEMPTS; $attempt++) {
            $number = sprintf('CAA-%d-%s', $year, $this->randomCode());

            if (! Certificate::where('unique_number', $number)->exists()) {
                return $number;
            }
        }

        throw new RuntimeException('Impossible de générer un numéro de certificat unique.');
    }

    private function randomCode(): string
    {
        $max = strlen(self::ALPHABET) - 1;
        $code = '';

        for ($i = 0; $i < self::CODE_LENGTH; $i++) {
            // random_int() : générateur cryptographiquement sûr.
            $code .= self::ALPHABET[random_int(0, $max)];
        }

        return $code;
    }
}
