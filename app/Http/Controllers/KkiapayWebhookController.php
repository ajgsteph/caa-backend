<?php

namespace App\Http\Controllers;

use App\Actions\Payment\ConfirmPaymentAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Reçoit les événements de paiement KKiaPay.
 *
 * Route publique (KKiaPay n'est pas authentifié chez nous) protégée par la
 * signature `x-kkiapay-secret`. La confirmation réelle est faite dans
 * ConfirmPaymentAction via verifyTransaction (on ne fait pas confiance au payload).
 */
class KkiapayWebhookController extends Controller
{
    public function handle(Request $request, ConfirmPaymentAction $confirmPayment): JsonResponse
    {
        if (! $this->signatureIsValid($request)) {
            Log::warning('[KKiaPay] signature webhook invalide', [
                'ip' => $request->ip(),
            ]);

            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $confirmPayment->execute($request->json()->all());

        // On répond toujours 200 aux événements signés pour éviter les relances.
        return response()->json(['received' => true]);
    }

    private function signatureIsValid(Request $request): bool
    {
        $secret = (string) config('services.kkiapay.webhook_secret');
        $provided = (string) $request->header('x-kkiapay-secret', '');

        if ($secret === '' || $provided === '') {
            return false;
        }

        // KKiaPay signe le corps brut avec le secret webhook (HMAC-SHA256).
        $expectedHmac = hash_hmac('sha256', $request->getContent(), $secret);

        // Tolérance : certaines configs renvoient directement le secret en clair.
        return hash_equals($expectedHmac, $provided) || hash_equals($secret, $provided);
    }
}
