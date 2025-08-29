<?php

namespace App\Mail;

use App\Models\RegistrationRequest;
use App\Models\User;
use App\Models\Classe;
use App\Models\Etablissement;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewRegistrationRequest extends Mailable
{
    use Queueable, SerializesModels;

    public RegistrationRequest $requestModel;
    public string $approveUrl;
    public string $rejectUrl;
    public int $usersCount;

    public function __construct(RegistrationRequest $requestModel, string $approveUrl, string $rejectUrl)
    {
        $this->requestModel = $requestModel;
        $this->approveUrl = $approveUrl;
        $this->rejectUrl = $rejectUrl;
        $this->usersCount = User::count();
    }

    public function build()
    {
        $meta = is_array($this->requestModel->meta) ? $this->requestModel->meta : [];

        $classeName = null;
        $etabName = null;

        if (!empty($meta['classe_id'])) {
            $classe = Classe::find((int) $meta['classe_id']);
            if ($classe) {
                $classeName = $classe->nom ?? ($classe->libelle ?? ('Classe ID '.$classe->id));
            }
        }
        if (!empty($meta['etablissement_id'])) {
            $etab = Etablissement::find((int) $meta['etablissement_id']);
            if ($etab) {
                $etabName = $etab->nom ?? ($etab->name ?? ('Etablissement ID '.$etab->id));
            }
        }

        return $this->subject('Nouvelle demande de création de compte')
            ->view('emails.registration_request')
            ->with([
                'rr' => $this->requestModel,
                'approveUrl' => $this->approveUrl,
                'rejectUrl' => $this->rejectUrl,
                'usersCount' => $this->usersCount,
                'classeName' => $classeName,
                'etabName' => $etabName,
            ]);
    }
}
