<?php

namespace App\Http\Controllers;

use App\Actions\Certificate\PreparePdfDownloadAction;
use App\Models\Certificate;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * @group Public verification
 *
 * Téléchargement du PDF d'un certificat via une URL signée temporaire.
 */
class DownloadController extends Controller
{
    /**
     * Télécharger le PDF (URL signée)
     *
     * URL obtenue via `GET /api/v1/certificates/{id}/download-link`. Lien valide 1 h.
     * Retourne le PDF du certificat. 404 si le certificat est révoqué ou si le PDF n'a pas
     * encore été généré.
     *
     * @unauthenticated
     *
     * @urlParam certificate integer required ID du certificat. Example: 1
     * @queryParam expires integer required Timestamp d'expiration. Example: 1777159343
     * @queryParam signature string required Signature HMAC. Example: 70916c5c1526696627504dd5e90866c163236ced51945ea198887fb2f5da57d1
     *
     * @response 200 scenario="success" PDF binary stream
     * @response 403 scenario="invalid signature" {"message": "Invalid signature."}
     * @response 404 scenario="revoked or not ready" {"message": "Le PDF du certificat n'est pas encore disponible."}
     */
    public function show(Certificate $certificate, PreparePdfDownloadAction $action): BinaryFileResponse
    {
        $payload = $action->execute($certificate);

        return response()->download($payload['path'], $payload['filename'], [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
