<?php

namespace App\Jobs;

use App\Models\Certificate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GenerateQrCodeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public int $certificateId) {}

    public function handle(): void
    {
        $certificate = Certificate::find($this->certificateId);
        if ($certificate === null) {
            return;
        }

        // Nom de fichier aléatoire (non devinable), réutilisé tel quel si déjà généré.
        $relativePath = $certificate->qr_code_path ?: 'qrcodes/'.Str::random(40).'.png';

        $png = QrCode::format('png')
            ->size(400)
            ->margin(1)
            ->errorCorrection('H')
            ->generate($certificate->verification_url);

        Storage::disk('public')->put($relativePath, $png);

        $certificate->update(['qr_code_path' => $relativePath]);
    }
}
