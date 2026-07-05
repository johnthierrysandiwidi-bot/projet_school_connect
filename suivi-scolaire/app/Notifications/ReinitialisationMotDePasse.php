<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReinitialisationMotDePasse extends Notification
{
    public function __construct(public string $token)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $url = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return (new MailMessage)
            ->subject('Réinitialisation de votre mot de passe — Suivi Scolaire')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Vous recevez cet email car une demande de réinitialisation de mot de passe a été effectuée pour votre compte.')
            ->action('Réinitialiser le mot de passe', $url)
            ->line('Ce lien de réinitialisation expirera dans 60 minutes.')
            ->line("Si vous n'êtes pas à l'origine de cette demande, vous pouvez ignorer cet email sans danger.");
    }
}
