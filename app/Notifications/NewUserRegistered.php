<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Enseignant;
use App\Models\Classe;
use App\Models\Etablissement;
use App\Models\Session as AcademicSession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewUserRegistered extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public User $user, public ?Enseignant $enseignant = null)
    {
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        // Get total number of users
        $totalUsers = User::count();
        
        // Resolve human-readable names
        $classeName = null;
        $etabName = null;
        $sessionName = null;
        if ($this->enseignant) {
            $classeName = optional(Classe::find($this->enseignant->classe_id))->nom;
            $etabName = optional(Etablissement::find($this->enseignant->etablissement_id))->nom;
            $sessionName = optional(AcademicSession::find($this->enseignant->session_id))->nom;
        }

        $mail = (new MailMessage)
            ->subject('Un nouveau compte a été créé sur votre plateforme')
            ->greeting('Bonjour,')
            ->line('Un nouveau compte a été créé sur votre plateforme avec les informations suivantes :')
            ->line('• Utilisateur: ' . $this->user->name)
            ->line('• Email: ' . $this->user->email);

        if ($this->enseignant) {
            $mail->line('• Enseignant: ' . trim(($this->enseignant->nom ?? '') . ' ' . ($this->enseignant->prenom ?? '')))
                 ->line('• Matricule: ' . ($this->enseignant->matricule ?? ''))
                 ->line('• Établissement: ' . ($etabName ?? ('#' . $this->enseignant->etablissement_id)))
                 ->line('• Classe: ' . ($classeName ?? ('#' . $this->enseignant->classe_id)))
                 ->line('• Session académique: ' . ($sessionName ?? ('#' . $this->enseignant->session_id)));
        }

        return $mail
            ->line('Date de création: ' . now()->toDateTimeString())
            ->line('\n---')
            ->line("Nombre total de comptes utilisateurs: $totalUsers");
    }
}
