<?php

namespace App\Http\Controllers;

use App\Mail\NewRegistrationRequest;
use App\Models\RegistrationRequest;
use App\Models\User;
use App\Models\Enseignant;
use App\Models\Classe;
use App\Models\Etablissement;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class RegistrationRequestController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email:rfc,dns|max:255|unique:users,email|unique:registration_requests,email',
            'password' => 'required|string|min:8',
            // Optional teacher fields at request time
            'nom' => 'nullable|string|max:50',
            'prenom' => 'nullable|string|max:50',
            'matricule' => 'nullable|string|max:20',
            'etablissement_id' => 'nullable|integer',
            'classe_id' => 'nullable|integer',
            'invite_code' => 'nullable|string',
        ]);

        $rr = RegistrationRequest::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'meta' => $request->except(['password']),
        ]);

        $approveUrl = URL::temporarySignedRoute('registration.approve', now()->addDays(2), ['id' => $rr->id]);
        $rejectUrl  = URL::temporarySignedRoute('registration.reject', now()->addDays(2), ['id' => $rr->id]);

        $adminEmail = env('ADMIN_NOTIFY_EMAIL', 'tidjouongbryan@gmail.com');
        Mail::to($adminEmail)->send(new NewRegistrationRequest($rr, $approveUrl, $rejectUrl));

        return redirect()->route('login')->with('status', 'Votre demande de création de compte a été envoyée. Elle sera validée par l\'administrateur.');
    }

    public function approve(Request $request, $id)
    {
        if (!$request->hasValidSignature()) {
            abort(401, 'Lien expiré ou invalide');
        }

        $rr = RegistrationRequest::findOrFail($id);
        $meta = is_array($rr->meta) ? $rr->meta : [];

        $user = User::firstOrCreate(
            ['email' => $rr->email],
            ['name' => $rr->name, 'password' => $rr->password]
        );

        $currentSession = Session::orderByDesc('date_debut')->first();
        $currentSessionId = $currentSession?->id;

        $nom = $meta['nom'] ?? null;
        $prenom = $meta['prenom'] ?? null;
        $matricule = $meta['matricule'] ?? null;
        $etablissementId = isset($meta['etablissement_id']) ? (int)$meta['etablissement_id'] : null;
        $classeId = isset($meta['classe_id']) ? (int)$meta['classe_id'] : null;

        if ($nom && $prenom && $matricule && $etablissementId && $classeId && $currentSessionId) {
            $classe = Classe::find($classeId);
            $etab = Etablissement::find($etablissementId);
            if ($classe && $etab && (int)$classe->etablissement_id === (int)$etablissementId && (int)$classe->session_id === (int)$currentSessionId) {
                Enseignant::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'nom' => $nom,
                        'prenom' => $prenom,
                        'matricule' => $matricule,
                        'classe_id' => $classeId,
                        'session_id' => $currentSessionId,
                        'etablissement_id' => $etablissementId,
                    ]
                );
            }
        }

        $rr->delete();

        return redirect()->route('login')->with('status', 'Compte approuvé et créé pour ' . $user->email);
    }

    public function reject(Request $request, $id)
    {
        if (!$request->hasValidSignature()) {
            abort(401, 'Lien expiré ou invalide');
        }

        $rr = RegistrationRequest::findOrFail($id);
        $email = $rr->email;
        $rr->delete();

        return redirect()->route('login')->with('status', 'Demande rejetée pour ' . $email);
    }
}
