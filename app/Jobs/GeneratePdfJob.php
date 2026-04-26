<?php

namespace App\Jobs;

use App\Models\Certificate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GeneratePdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public int $certificateId) {}

    public function handle(): void
    {
        $certificate = Certificate::with(['artwork', 'client', 'artist.artistProfile'])
            ->find($this->certificateId);

        if ($certificate === null) {
            return;
        }

        $relativePath = 'certificates/'.$certificate->unique_number.'.pdf';

        $pdf = Pdf::loadView('pdf.certificate', [
            'certificate' => $certificate,
            'qrCodeAbsolutePath' => $certificate->qr_code_path
                ? Storage::disk('public')->path($certificate->qr_code_path)
                : null,
            'artworkAbsolutePath' => $certificate->artwork?->image_path
                ? Storage::disk('public')->path($certificate->artwork->image_path)
                : null,
        ])->setPaper('a4', 'portrait');

        Storage::disk('local')->put($relativePath, $pdf->output());

        $certificate->update(['pdf_path' => $relativePath]);
    }
}
