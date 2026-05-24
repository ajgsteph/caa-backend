<?php

namespace App\Actions\Certificate;

use App\Actions\Client\FindOrCreateClientAction;
// use App\Actions\Payment\InitiatePaymentAction;
use App\Enums\CertificateStatus;
// use App\Enums\PaymentMethod;
use App\Jobs\GeneratePdfJob;
use App\Jobs\GenerateQrCodeJob;
use App\Jobs\SendCertificateEmailJob;
use App\Models\Artwork;
use App\Models\Certificate;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;

class CreateCertificateAction
{
  public function __construct(
    private readonly FindOrCreateClientAction $findOrCreateClient,
    private readonly GenerateUniqueNumberAction $generateUniqueNumber,
    // private readonly InitiatePaymentAction $initiatePayment,
  ) {}

  public function execute(User $artist, array $payload): Certificate
  {
    return DB::transaction(function () use ($artist, $payload): Certificate {
      $artwork = Artwork::findOrFail($payload['artwork_id']);
      $client = $this->findOrCreateClient->execute($payload['client']);
      $number = $this->generateUniqueNumber->execute();

      $certificate = Certificate::create([
        'unique_number' => $number,
        'artwork_id' => $artwork->id,
        'client_id' => $client->id,
        'artist_id' => $artist->id,
        'verification_url' => rtrim(config('app.url'), '/') . '/api/v1/verify/' . $number,
        'status' => CertificateStatus::PENDING,
      ]);

      // $this->initiatePayment->execute(
      //     $certificate,
      //     PaymentMethod::from($payload['payment']['method'])
      // );

      Bus::chain([
        new GenerateQrCodeJob($certificate->id),
        new GeneratePdfJob($certificate->id),
        new SendCertificateEmailJob($certificate->id),
      ])->dispatch();

      return $certificate->load(['artwork', 'client', 'artist.artistProfile', 'payment']);
    });
  }
}
