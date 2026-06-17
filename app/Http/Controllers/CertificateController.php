<?php

namespace App\Http\Controllers;

use App\Actions\Certificate\CreateCertificateAction;
use App\Actions\Certificate\RevokeCertificateAction;
use App\Actions\Payment\InitiatePaymentAction;
use App\Enums\CertificateStatus;
use App\Http\Requests\Certificate\CreateCertificateRequest;
use App\Http\Requests\Certificate\RevokeCertificateRequest;
use App\Http\Resources\CertificateResource;
use App\Http\Resources\PaymentResource;
use App\Models\Certificate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;

/**
 * @group Certificates
 *
 * Création, consultation, révocation et téléchargement des certificats d'authenticité.
 *
 * @authenticated
 */
class CertificateController extends Controller
{
    /**
     * Lister mes certificats
     *
     * Retourne les certificats de l'artiste authentifié, paginés par 15.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $certificates = $request->user()
            ->certificates()
            ->with(['artwork', 'client', 'payment'])
            ->latest()
            ->paginate(15);

        return CertificateResource::collection($certificates);
    }

    /**
     * Créer un certificat
     *
     * Réservé au rôle `artist`. Crée  le client + le certificat (status `PENDING`),
     * initie le paiement (10 000 FCFA) et déclenche le pipeline asynchrone de génération
     * (QR, PDF, email). Le certificat passe à `ACTIVE` une fois le paiement confirmé.
     *
     * @bodyParam artwork_id integer required ID de l'œuvre existante appartenant à l'artiste. Example: 1
     * @bodyParam client[last_name] string required Nom du client. Example: Ndiaye
     * @bodyParam client[first_name] string required Prénom du client. Example: Mamadou
     * @bodyParam client[email] string required Email du client. Example: client@test.sn
     * @bodyParam client[phone] string Téléphone du client. Example: +221770000099
     * @bodyParam payment[method] string required Méthode de paiement. Example: WAVE
     *
     * @response 202 scenario="accepted" {"data": {"unique_number": "CAA-2026-0001", "status": "PENDING"}}
     */
    public function store(CreateCertificateRequest $request, CreateCertificateAction $action): JsonResponse
    {
        $certificate = $action->execute(
            $request->user(),
            $request->validated(),
        );

        return CertificateResource::make($certificate)->response()->setStatusCode(202);
    }

    /**
     * Voir un certificat
     *
     * L'artiste voit ses propres certificats ; les admins voient tout.
     *
     * @urlParam certificate integer required ID du certificat. Example: 1
     */
    public function show(Request $request, Certificate $certificate): CertificateResource
    {
        Gate::authorize('view', $certificate);

        return CertificateResource::make(
            $certificate->load(['artwork', 'client', 'artist.artistProfile', 'payment'])
        );
    }

    /**
     * Obtenir un lien signé de téléchargement PDF
     *
     * Retourne une URL signée valide 1 h vers `/api/v1/download/{id}`.
     *
     * @urlParam certificate integer required ID du certificat. Example: 1
     *
     * @response 200 {"url": "http://localhost/api/v1/download/1?expires=...&signature=...", "expires_in_seconds": 3600}
     */
    public function downloadLink(Request $request, Certificate $certificate): JsonResponse
    {
        Gate::authorize('downloadLink', $certificate);

        $url = URL::temporarySignedRoute(
            'download.show',
            now()->addHour(),
            ['certificate' => $certificate->id]
        );

        return response()->json(['url' => $url, 'expires_in_seconds' => 3600]);
    }

    /**
     * Relancer le paiement
     *
     * Crée une nouvelle tentative de paiement (PENDING) pour un certificat encore
     * en attente, après un échec. Renvoie le paiement à régler via le widget KKiaPay.
     *
     * @urlParam certificate integer required ID du certificat. Example: 1
     *
     * @response 201 {"data": {"id": 46, "amount": 10000, "status": "PENDING"}}
     */
    public function retryPayment(Request $request, Certificate $certificate, InitiatePaymentAction $initiatePayment): JsonResponse
    {
        Gate::authorize('view', $certificate);

        if ($certificate->status !== CertificateStatus::PENDING) {
            return response()->json([
                'message' => "Ce certificat n'est pas en attente de paiement.",
            ], 422);
        }

        $method = $certificate->payment?->method ?? InitiatePaymentAction::defaultMethod();
        $payment = $initiatePayment->execute($certificate, $method);

        return PaymentResource::make($payment)->response()->setStatusCode(201);
    }

    /**
     * Révoquer un certificat
     *
     * Réservé aux rôles `admin` et `super_admin`. Le motif est enregistré et notifié
     * par email à l'artiste et au client.
     *
     * @urlParam certificate integer required ID du certificat. Example: 1
     *
     * @bodyParam reason string required Motif détaillé (≥ 10 caractères). Example: Œuvre non authentique selon expertise.
     */
    public function revoke(RevokeCertificateRequest $request, Certificate $certificate, RevokeCertificateAction $action): CertificateResource
    {
        $certificate = $action->execute($certificate, $request->string('reason'), $request->user());

        return CertificateResource::make($certificate);
    }
}
