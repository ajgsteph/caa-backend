<?php

namespace App\Actions\Certificate;

use App\Actions\Artwork\SaveArtworkAction;
use App\Actions\Client\FindOrCreateClientAction;
use App\Actions\Payment\InitiatePaymentAction;
use App\Enums\CertificateStatus;
use App\Enums\PaymentMethod;
use App\Models\Certificate;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class CreateCertificateAction
{
    public function __construct(
        private readonly SaveArtworkAction $saveArtwork,
        private readonly FindOrCreateClientAction $findOrCreateClient,
        private readonly GenerateUniqueNumberAction $generateUniqueNumber,
        private readonly InitiatePaymentAction $initiatePayment,
    ) {}

    public function execute(User $artist, array $payload, ?UploadedFile $image): Certificate
    {
        return DB::transaction(function () use ($artist, $payload, $image): Certificate {
            $artwork = $this->saveArtwork->execute($artist, $payload['artwork'], $image);
            $client = $this->findOrCreateClient->execute($payload['client']);
            $number = $this->generateUniqueNumber->execute();

            $certificate = Certificate::create([
                'unique_number' => $number,
                'artwork_id' => $artwork->id,
                'client_id' => $client->id,
                'artist_id' => $artist->id,
                'verification_url' => rtrim(config('app.url'), '/').'/api/v1/verify/'.$number,
                'status' => CertificateStatus::PENDING,
            ]);

            $this->initiatePayment->execute(
                $certificate,
                PaymentMethod::from($payload['payment']['method'])
            );

            return $certificate->load(['artwork', 'client', 'artist.artistProfile', 'payment']);
        });
    }
}
