<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Http\Request;

class UserLoggedIn extends Notification implements ShouldQueue
{
    use Queueable;

    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $ip = $this->request->ip();
        $agent = substr((string) $this->request->header('User-Agent'), 0, 255);
        $time = now()->format('Y-m-d H:i:s');

        return (new MailMessage)
            ->subject('Connexion à votre compte')
            ->greeting('Bonjour '.$notifiable->name)
            ->line('Une connexion à votre compte a été effectuée avec succès.')
            ->line('Date et heure: '.$time)
            ->line('Adresse IP: '.$ip)
            ->line('Navigateur/Appareil: '.$agent)
            ->line('Si vous n’êtes pas à l’origine de cette connexion, veuillez changer votre mot de passe immédiatement.');
    }
}
