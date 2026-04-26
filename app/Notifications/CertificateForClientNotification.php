<?php

namespace App\Notifications;

use App\Models\Certificate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CertificateForClientNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Certificate $certificate) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $artistName = $this->certificate->artist?->artistProfile?->artist_name
            ?? trim(($this->certificate->artist?->first_name ?? '').' '.($this->certificate->artist?->last_name ?? ''));

        return (new MailMessage())
            ->subject('Certificat d\'authenticité de votre œuvre — '.$this->certificate->unique_number)
            ->greeting('Bonjour')
            ->line('L\'artiste '.$artistName.' a émis un certificat d\'authenticité pour l\'œuvre que vous avez acquise.')
            ->line('Numéro : '.$this->certificate->unique_number)
            ->line('Œuvre : '.$this->certificate->artwork?->title)
            ->action('Vérifier l\'authenticité', $this->certificate->verification_url)
            ->line('Conservez ce lien : il atteste de l\'authenticité de votre œuvre.');
    }
}
