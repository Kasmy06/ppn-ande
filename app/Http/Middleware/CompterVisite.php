<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Compte les visites du site public, de façon anonyme : seul un total journalier est conservé.
 * Une même session n'est comptée qu'une fois par jour (marqueur en session, sans adresse IP).
 */
class CompterVisite
{
    public function handle(Request $request, Closure $next): Response
    {
        $reponse = $next($request);

        if (! $request->isMethod('GET') || $reponse->getStatusCode() !== 200) {
            return $reponse;
        }

        $jour = today()->toDateString();
        $cle = 'visite_comptee_'.$jour;

        if (! $request->session()->has($cle)) {
            $request->session()->put($cle, true);
            DB::table('visites_journalieres')->insertOrIgnore(['jour' => $jour, 'nombre' => 0]);
            DB::table('visites_journalieres')->where('jour', $jour)->increment('nombre');
        }

        return $reponse;
    }
}
