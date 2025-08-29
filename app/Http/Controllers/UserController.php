<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Enseignant;
use App\Models\Classe;
use App\Models\Etablissement;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewUserRegistered;

class UserController extends Controller
{
    public function create()
    {
        // Determine current session for filtering classes
        $currentSessionId = session('session_id');
        if (!$currentSessionId) {
            $latestSession = Session::orderByDesc('date_debut')->first();
            $currentSessionId = $latestSession?->id;
        }
        if (!$currentSessionId) {
            return redirect()->back()->with('error', 'Aucune session académique n\'est définie. Veuillez créer/sélectionner une session avant de créer un compte.');
        }

        $etablissements = Etablissement::orderBy('nom')->get(['id','nom']);
        $classes = Classe::where('session_id', $currentSessionId)
            ->orderBy('nom')
            ->get(['id','nom','etablissement_id','session_id']);
        return view('users.create', compact('etablissements','classes','currentSessionId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'invite_code' => ['required', 'string'],
            // Enseignant fields
            'nom' => ['required', 'string', 'max:50'],
            'prenom' => ['required', 'string', 'max:50'],
            'matricule' => ['required', 'string', 'max:20'],
            'etablissement_id' => ['required', 'integer', 'exists:etablissements,id'],
            'classe_id' => ['required', 'integer', 'exists:classes,id'],
        ]);

        // Check invite/registration code from config
        $expectedCode = config('app.registration_code');
        if (empty($expectedCode) || !hash_equals((string)$expectedCode, (string)$validated['invite_code'])) {
            return back()->withErrors(['invite_code' => "Code d'invitation invalide."])->withInput();
        }

        // Ensure the selected class belongs to the selected etablissement
        $classe = Classe::findOrFail($validated['classe_id']);
        if ((int)$classe->etablissement_id !== (int)$validated['etablissement_id']) {
            return back()->withErrors(['classe_id' => 'La classe sélectionnée n’appartient pas à cet établissement.'])->withInput();
        }

        // Determine session_id: prefer current session from app session, else latest session
        $currentSessionId = session('session_id');
        if (!$currentSessionId) {
            $latestSession = Session::orderByDesc('date_debut')->first();
            $currentSessionId = $latestSession?->id;
        }
        if (!$currentSessionId) {
            return back()->withErrors(['session' => 'Aucune session définie. Veuillez créer une session avant de créer un enseignant.'])->withInput();
        }

        // Ensure class belongs to the current academic session
        if ((int)$classe->session_id !== (int)$currentSessionId) {
            return back()->withErrors(['classe_id' => 'La classe sélectionnée n’appartient pas à la session académique courante.'])->withInput();
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $enseignant = Enseignant::create([
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'matricule' => $validated['matricule'],
            'classe_id' => $validated['classe_id'],
            'session_id' => $currentSessionId,
            'user_id' => $user->id,
            'etablissement_id' => $validated['etablissement_id'],
        ]);

        // Notify admin by email about new registration
        try {
            $adminEmail = config('app.admin_notify_email');
            if (!empty($adminEmail)) {
                Notification::route('mail', $adminEmail)
                    ->notify(new NewUserRegistered($user, $enseignant));
            }
        } catch (\Throwable $e) {
            \Log::warning('Admin notification failed: '.$e->getMessage());
        }

        return redirect()->route('users.create')->with('status', 'Compte enseignant créé et associé à la classe et à l’établissement.');
    }
}
