<?php

namespace App\Actions\Certificate;

use App\Enums\CertificateStatus;
use App\Models\Certificate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PreparePdfDownloadAction
{
    /**
     * @return array{path: string, filename: string}
     */
    public function execute(Certificate $certificate): array
    {
        if ($certificate->status === CertificateStatus::REVOKED) {
            throw new NotFoundHttpException('Ce certificat a été révoqué.');
        }

        if (! $certificate->pdf_path || ! Storage::disk('local')->exists($certificate->pdf_path)) {
            throw new NotFoundHttpException('Le PDF du certificat n\'est pas encore disponible.');
        }

        return [
            'path' => Storage::disk('local')->path($certificate->pdf_path),
            'filename' => $certificate->unique_number.'.pdf',
        ];
    }
}
