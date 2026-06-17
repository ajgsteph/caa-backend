<?php

namespace App\Actions\Payment;

use App\Enums\PaymentStatus;
use App\Jobs\GenerateCertificateJob;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Kkiapay\Kkiapay;

/**
 * Confirme (ou rejette) un paiement à partir d'un événement webhook KKiaPay.
 *
 * Sécurité : on ne fait JAMAIS confiance au payload reçu. On revérifie la
 * transaction directement auprès de KKiaPay (verifyTransaction) avant toute
 * mise à jour. Idempotent : un paiement déjà confirmé est ignoré.
 */
class ConfirmPaymentAction
{
    public function __construct(private readonly Kkiapay $kkiapay) {}

    /**
     * @param  array<string,mixed>  $payload  Corps JSON du webhook KKiaPay.
     */
    public function execute(array $payload): void
    {
        $transactionId = $payload['transactionId'] ?? null;

        if (! is_string($transactionId) || $transactionId === '') {
            Log::warning('[KKiaPay] webhook sans transactionId', ['payload' => $payload]);

            return;
        }

        $payment = $this->resolvePayment($payload, $transactionId);

        if ($payment === null) {
            Log::warning('[KKiaPay] paiement introuvable pour la transaction', [
                'transaction_id' => $transactionId,
            ]);

            return;
        }

        // Idempotence : ne pas retraiter un paiement déjà confirmé.
        if ($payment->status === PaymentStatus::SUCCESSFUL) {
            return;
        }

        // Vérification autoritaire auprès de KKiaPay.
        $verification = $this->kkiapay->verifyTransaction($transactionId);
        $status = is_object($verification) ? ($verification->status ?? null) : null;
        $verifiedAmount = is_object($verification) ? ($verification->amount ?? null) : null;

        $isSuccess = $status === 'SUCCESS'
            && $verifiedAmount !== null
            && (float) $verifiedAmount === (float) $payment->amount;

        if (! $isSuccess) {
            $payment->update(['status' => PaymentStatus::FAILED]);

            Log::info('[KKiaPay] paiement échoué/non confirmé', [
                'payment_id' => $payment->id,
                'transaction_id' => $transactionId,
                'verified_status' => $status,
                'verified_amount' => $verifiedAmount,
            ]);

            return;
        }

        DB::transaction(function () use ($payment, $transactionId): void {
            $payment->update([
                'status' => PaymentStatus::SUCCESSFUL,
                'paid_at' => now(),
                'transaction_reference' => $transactionId,
            ]);
        });

        GenerateCertificateJob::dispatch($payment->certificate_id);
    }

    /**
     * Retrouve la tentative de paiement : via le `data` du widget renvoyé dans
     * `stateData` (payment_id), sinon via la référence de transaction déjà stockée.
     *
     * @param  array<string,mixed>  $payload
     */
    private function resolvePayment(array $payload, string $transactionId): ?Payment
    {
        $paymentId = $this->extractPaymentId($payload['stateData'] ?? null);

        if ($paymentId !== null) {
            return Payment::find($paymentId);
        }

        return Payment::where('transaction_reference', $transactionId)->first();
    }

    /**
     * `stateData` est l'écho du `data` passé au widget. Il peut arriver sous
     * forme de tableau, d'objet JSON encodé en string, ou contenir directement
     * `payment_id`.
     */
    private function extractPaymentId(mixed $stateData): ?int
    {
        if (is_string($stateData)) {
            $decoded = json_decode($stateData, true);
            $stateData = is_array($decoded) ? $decoded : null;
        }

        if (is_array($stateData) && isset($stateData['payment_id']) && is_numeric($stateData['payment_id'])) {
            return (int) $stateData['payment_id'];
        }

        return null;
    }
}
