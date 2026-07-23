<?php

namespace App\Http\Controllers;

use App\Models\Etablissement;
use App\Models\Visiteur;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonthNoOverflow()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonthNoOverflow()->endOfMonth();

        $totalVisiteurs = Visiteur::count();
        $totalEleves = Visiteur::where('type', 'Élève')->count();
        $totalFonctionnaires = Visiteur::where('type', 'Fonctionnaire')->count();
        $totalExternes = Visiteur::where('type', 'Externe')->count();

        $trend = function (?string $type = null) use ($startOfMonth, $startOfLastMonth, $endOfLastMonth) {
            $query = fn ($from, $to) => Visiteur::when($type, fn ($q) => $q->where('type', $type))
                ->whereBetween('date_visite', [$from, $to])
                ->count();

            $ceMois = $query($startOfMonth, now());
            $moisDernier = $query($startOfLastMonth, $endOfLastMonth);

            if ($moisDernier === 0) {
                return $ceMois > 0 ? 100 : 0;
            }

            return (int) round((($ceMois - $moisDernier) / $moisDernier) * 100);
        };

        $garcons = Visiteur::where('sexe', 'M')->count();
        $filles = Visiteur::where('sexe', 'F')->count();

        $topEtablissements = Etablissement::withCount('visiteurs')
            ->orderByDesc('visiteurs_count')
            ->take(6)
            ->get();

        $moisRange = collect(range(0, 11))->map(fn ($i) => $now->copy()->startOfYear()->addMonths($i));
        $mensuel = $moisRange->map(function ($mois) {
            return Visiteur::whereYear('date_visite', $mois->year)
                ->whereMonth('date_visite', $mois->month)
                ->count();
        });

        $derniersVisiteurs = Visiteur::with('etablissement')
            ->latest('created_at')
            ->take(5)
            ->get();

        $activiteRecente = Visiteur::with('auteur')
            ->latest('created_at')
            ->take(5)
            ->get();

        return view('dashboard', [
            'totalVisiteurs' => $totalVisiteurs,
            'totalEleves' => $totalEleves,
            'totalFonctionnaires' => $totalFonctionnaires,
            'totalExternes' => $totalExternes,
            'trendVisiteurs' => $trend(),
            'trendEleves' => $trend('Élève'),
            'trendFonctionnaires' => $trend('Fonctionnaire'),
            'trendExternes' => $trend('Externe'),
            'garcons' => $garcons,
            'filles' => $filles,
            'topEtablissements' => $topEtablissements,
            'moisLabels' => $moisRange->map(fn ($m) => ucfirst($m->translatedFormat('M'))),
            'mensuel' => $mensuel,
            'derniersVisiteurs' => $derniersVisiteurs,
            'activiteRecente' => $activiteRecente,
        ]);
    }
}
