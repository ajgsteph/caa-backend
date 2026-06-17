<?php

namespace App\Jobs;

use App\Enums\CertificateStatus;
use App\Models\Certificate;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Bus;

/**
 * Lancé uniquement après confirmation d'un paiement (webhook KKiaPay).
 * Active le certificat puis enchaîne la génération QR -> PDF -> email.
 */
class GenerateCertificateJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public int $certificateId) {}

    public function handle(): void
    {
        $certificate = Certificate::find($this->certificateId);

        if ($certificate === null) {
            return;
        }

        // On active d'abord pour que le PDF rende le bon statut, puis on génère
        // les artefacts via le pipeline déjà éprouvé (QR PNG -> PDF -> emails).
        $certificate->update([
            'status' => CertificateStatus::ACTIVE,
            'certified_at' => now(),
        ]);

        Bus::chain([
            new GenerateQrCodeJob($certificate->id),
            new GeneratePdfJob($certificate->id),
            new SendCertificateEmailJob($certificate->id),
        ])->dispatch();
    }
}
