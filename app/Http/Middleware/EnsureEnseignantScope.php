<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Enseignant;

class EnsureEnseignantScope
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $enseignant = Enseignant::where('user_id', $user->id)->first();
        if (!$enseignant) {
            abort(403, 'Aucun profil enseignant lié à cet utilisateur.');
        }

        // Optionnel: fixer la session académique courante à celle de l'enseignant
        if (!session('session_id')) {
            session(['session_id' => $enseignant->session_id]);
        }

        // Vérifier les paramètres de route s'ils existent
        $route = $request->route();
        $params = $route ? $route->parameters() : [];
        if (isset($params['session_id']) && (int)$params['session_id'] !== (int)$enseignant->session_id) {
            abort(403, 'Accès refusé à une autre session.');
        }
        if (isset($params['classe_id']) && (int)$params['classe_id'] !== (int)$enseignant->classe_id) {
            abort(403, 'Accès refusé à une autre classe.');
        }
        if (isset($params['classe']) && method_exists($params['classe'], 'getAttribute')) {
            if ((int)$params['classe']->getAttribute('id') !== (int)$enseignant->classe_id) {
                abort(403, 'Accès refusé à une autre classe.');
            }
        }

        // Partager l'enseignant pour usage ultérieur
        $request->attributes->set('enseignant', $enseignant);

        return $next($request);
    }
}
