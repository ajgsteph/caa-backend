<?php

namespace App\Actions\Certificate;

use App\Enums\CertificateStatus;
use App\Models\Certificate;
use App\Models\User;
use App\Notifications\CertificateRevokedNotification;
use Illuminate\Support\Facades\Notification;

class RevokeCertificateAction
{
    public function execute(Certificate $certificate, string $reason, User $admin): Certificate
    {
        $certificate->update([
            'status' => CertificateStatus::REVOKED,
            'revocation_reason' => $reason,
        ]);

        $certificate->load(['artist', 'client', 'artwork']);

        if ($certificate->artist) {
            $certificate->artist->notify(new CertificateRevokedNotification($certificate, $reason));
        }

        if ($certificate->client) {
            Notification::route('mail', $certificate->client->email)
                ->notify(new CertificateRevokedNotification($certificate, $reason));
        }

        return $certificate->fresh(['artwork', 'client', 'artist.artistProfile', 'payment']);
    }
}
