<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OptimizePerformance
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Optimiser les paramètres PHP pour les performances
        if (app()->environment('production')) {
            ini_set('max_execution_time', 300);
            ini_set('memory_limit', '512M');
        }

        // Activer la compression gzip si disponible
        if (extension_loaded('zlib')) {
            ini_set('zlib.output_compression', 1);
        }

        // Ajouter des headers de cache pour les ressources statiques
        $response = $next($request);

        if ($request->is('*.css') || $request->is('*.js') || $request->is('*.png') || $request->is('*.jpg') || $request->is('*.jpeg') || $request->is('*.gif')) {
            $response->header('Cache-Control', 'public, max-age=31536000');
        }

        return $response;
    }
} 