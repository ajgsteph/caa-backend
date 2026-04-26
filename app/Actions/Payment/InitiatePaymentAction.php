<?php

namespace App\Actions\Payment;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Jobs\VerifyPaymentJob;
use App\Models\Certificate;
use App\Models\Payment;

class InitiatePaymentAction
{
    public const CERTIFICATE_PRICE_FCFA = 10000.00;

    public function execute(Certificate $certificate, PaymentMethod $method): Payment
    {
        $payment = Payment::create([
            'certificate_id' => $certificate->id,
            'amount' => self::CERTIFICATE_PRICE_FCFA,
            'method' => $method,
            'status' => PaymentStatus::PENDING,
        ]);

        // TODO: integrate Wave / Orange Money / MTN MoMo / Bank card via real HTTP client.
        // For now we dispatch a stub job that simulates a successful confirmation
        // after a short delay. Replace VerifyPaymentJob with a real provider webhook
        // handler when integrating a payment gateway.
        VerifyPaymentJob::dispatch($payment->id);

        return $payment;
    }
}
