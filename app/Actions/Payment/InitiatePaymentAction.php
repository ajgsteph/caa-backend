<?php

namespace App\Actions\Payment;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Certificate;
use App\Models\Payment;

class InitiatePaymentAction
{
    public const CERTIFICATE_PRICE_FCFA = 10000.00;

    /** Méthode par défaut quand aucune tentative précédente n'existe. */
    public static function defaultMethod(): PaymentMethod
    {
        return PaymentMethod::MTN_MOMO;
    }

    /**
     * Crée une tentative de paiement (PENDING). La confirmation est pilotée par
     * le webhook KKiaPay (ConfirmPaymentAction) — aucun job n'est lancé ici.
     */
    public function execute(Certificate $certificate, PaymentMethod $method): Payment
    {
        return Payment::create([
            'certificate_id' => $certificate->id,
            'amount' => self::CERTIFICATE_PRICE_FCFA,
            'method' => $method,
            'status' => PaymentStatus::PENDING,
        ]);
    }
}
