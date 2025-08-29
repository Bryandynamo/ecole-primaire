<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ScopeEtablissementClasse
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        if ($user && $user->relationLoaded('enseignant') === false) {
            $user->load('enseignant');
        }
        $enseignant = $user?->enseignant;
        if ($enseignant) {
            // Share scope globally for views/controllers
            app()->instance('currentClasseId', $enseignant->classe_id);
            app()->instance('currentEtablissementId', $enseignant->etablissement_id ?? optional($enseignant->classe)->etablissement_id);
        }
        return $next($request);
    }
}
