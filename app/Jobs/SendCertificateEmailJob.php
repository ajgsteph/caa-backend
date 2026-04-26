<?php

namespace App\Jobs;

use App\Models\Certificate;
use App\Notifications\CertificateForClientNotification;
use App\Notifications\CertificateGeneratedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class SendCertificateEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public int $certificateId) {}

    public function handle(): void
    {
        $certificate = Certificate::with(['artwork', 'client', 'artist.artistProfile'])
            ->find($this->certificateId);

        if ($certificate === null) {
            return;
        }

        if ($certificate->artist) {
            $certificate->artist->notify(new CertificateGeneratedNotification($certificate));
        }

        if ($certificate->client) {
            Notification::route('mail', $certificate->client->email)
                ->notify(new CertificateForClientNotification($certificate));
        }
    }
}
