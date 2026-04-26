<?php

namespace App\Actions\Certificate;

use App\Models\Certificate;

class VerifyCertificateAction
{
    public function execute(string $uniqueNumber): Certificate
    {
        return Certificate::with(['artwork', 'artist.artistProfile', 'client'])
            ->where('unique_number', $uniqueNumber)
            ->firstOrFail();
    }
}
