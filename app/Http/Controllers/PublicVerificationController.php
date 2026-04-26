<?php

namespace App\Http\Controllers;

use App\Actions\Certificate\VerifyCertificateAction;
use App\Http\Resources\PublicCertificateResource;

/**
 * @group Public verification
 *
 * Endpoint public (sans authentification) pour vérifier l'authenticité d'un certificat.
 */
class PublicVerificationController extends Controller
{
    /**
     * Vérifier un certificat
     *
     * Retourne les informations publiques du certificat (sans données sensibles client/paiement).
     * Le statut indique si le certificat est `ACTIVE`, `REVOKED` ou `PENDING`.
     *
     * @unauthenticated
     *
     * @urlParam number string required Numéro unique du certificat (format `CAA-YYYY-NNNN`). Example: CAA-2026-0001
     *
     * @response 200 {
     *   "data": {
     *     "unique_number": "CAA-2026-0001",
     *     "status": "ACTIVE",
     *     "is_valid": true,
     *     "certified_at": "2026-04-25T22:18:53+00:00",
     *     "artwork": {"title": "Soleil de Dakar", "type": "PAINTING", "year": 2025},
     *     "artist": {"artist_name": "AWA-D"}
     *   }
     * }
     */
    public function show(string $number, VerifyCertificateAction $action): PublicCertificateResource
    {
        return PublicCertificateResource::make($action->execute($number));
    }
}
