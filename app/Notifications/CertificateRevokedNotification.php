<?php

namespace App\Notifications;

use App\Models\Certificate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CertificateRevokedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Certificate $certificate,
        public string $reason,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Certificat révoqué — '.$this->certificate->unique_number)
            ->error()
            ->greeting('Bonjour')
            ->line('Le certificat '.$this->certificate->unique_number.' a été révoqué.')
            ->line('Motif : '.$this->reason)
            ->line('Si vous pensez qu\'il s\'agit d\'une erreur, contactez l\'équipe CAA.');
    }
}
