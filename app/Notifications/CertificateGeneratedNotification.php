<?php

namespace App\Notifications;

use App\Models\Certificate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CertificateGeneratedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Certificate $certificate) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Votre certificat CAA est prêt — '.$this->certificate->unique_number)
            ->greeting('Bonjour '.($notifiable->first_name ?? ''))
            ->line('Votre certificat d\'authenticité a été généré avec succès.')
            ->line('Numéro : '.$this->certificate->unique_number)
            ->line('Œuvre : '.$this->certificate->artwork?->title)
            ->action('Vérifier le certificat', $this->certificate->verification_url)
            ->line('Vous pouvez télécharger le PDF depuis votre espace artiste.');
    }
}
