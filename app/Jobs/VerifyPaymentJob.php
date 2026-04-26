<?php

namespace App\Jobs;

use App\Enums\CertificateStatus;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;

class VerifyPaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public int $paymentId) {}

    public function handle(): void
    {
        $payment = Payment::with('certificate')->find($this->paymentId);

        if ($payment === null || $payment->status !== PaymentStatus::PENDING) {
            return;
        }

        // TODO: replace this stub with a real call to Wave / Orange Money / MTN MoMo /
        // bank-card provider. Today we just simulate a SUCCESSFUL payment after a
        // short delay so the rest of the pipeline (QR -> PDF -> email) can run.
        sleep(2);
        $simulatedSuccess = true;

        if (! $simulatedSuccess) {
            $payment->update([
                'status' => PaymentStatus::FAILED,
            ]);
            return;
        }

        $payment->update([
            'status' => PaymentStatus::SUCCESSFUL,
            'paid_at' => now(),
            'transaction_reference' => 'STUB-'.Str::upper(Str::random(12)),
        ]);

        $certificate = $payment->certificate;
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
