<?php

namespace App\Http\Controllers;

use App\Models\Etablissement;
use App\Models\Visiteur;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StatistiqueController extends Controller
{
    public function index(Request $request): View
    {
        $dateDebut = $request->filled('date_debut')
            ? Carbon::parse($request->query('date_debut'))->startOfDay()
            : now()->startOfYear();
        $dateFin = $request->filled('date_fin')
            ? Carbon::parse($request->query('date_fin'))->endOfDay()
            : now()->endOfYear();

        $base = fn () => Visiteur::query()
            ->whereBetween('date_visite', [$dateDebut, $dateFin])
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->query('type')))
            ->when($request->filled('sexe'), fn ($q) => $q->where('sexe', $request->query('sexe')))
            ->when($request->filled('etablissement_id'), fn ($q) => $q->where('etablissement_id', $request->query('etablissement_id')));

        $total = $base()->count();
        $eleves = $base()->where('type', 'Élève')->count();
        $garcons = $base()->where('sexe', 'M')->count();
        $filles = $base()->where('sexe', 'F')->count();

        $mois = collect();
        $cursor = $dateDebut->copy()->startOfMonth();
        while ($cursor->lte($dateFin)) {
            $mois->push($cursor->copy());
            $cursor->addMonth();
        }

        $evolutionMensuelle = $mois->map(fn ($m) => $base()
            ->whereYear('date_visite', $m->year)
            ->whereMonth('date_visite', $m->month)
            ->count());

        $genreParMois = $mois->map(function ($m) use ($base) {
            return [
                'garcons' => (clone $base())->where('sexe', 'M')->whereYear('date_visite', $m->year)->whereMonth('date_visite', $m->month)->count(),
                'filles' => (clone $base())->where('sexe', 'F')->whereYear('date_visite', $m->year)->whereMonth('date_visite', $m->month)->count(),
            ];
        });

        $repartitionType = [
            'Élève' => $base()->where('type', 'Élève')->count(),
            'Fonctionnaire' => $base()->where('type', 'Fonctionnaire')->count(),
            'Externe' => $base()->where('type', 'Externe')->count(),
        ];

        $topEtablissements = Etablissement::query()
            ->withCount(['visiteurs' => function ($q) use ($dateDebut, $dateFin, $request) {
                $q->whereBetween('date_visite', [$dateDebut, $dateFin])
                    ->when($request->filled('type'), fn ($q) => $q->where('type', $request->query('type')))
                    ->when($request->filled('sexe'), fn ($q) => $q->where('sexe', $request->query('sexe')));
            }])
            ->orderByDesc('visiteurs_count')
            ->take(5)
            ->get();

        $resumeEtablissements = $topEtablissements->map(fn ($e) => [
            'nom' => $e->nom,
            'total' => $e->visiteurs_count,
            'pourcentage' => $total > 0 ? round(($e->visiteurs_count / $total) * 100, 1) : 0,
        ]);

        $frequentationMensuelle = $mois->map(function ($m, $i) use ($evolutionMensuelle) {
            $courant = $evolutionMensuelle[$i];
            $precedent = $i > 0 ? $evolutionMensuelle[$i - 1] : null;
            $variation = $precedent ? ($precedent > 0 ? round((($courant - $precedent) / $precedent) * 100) : ($courant > 0 ? 100 : 0)) : null;

            return [
                'mois' => ucfirst($m->translatedFormat('F Y')),
                'total' => $courant,
                'variation' => $variation,
            ];
        });

        return view('statistiques.index', [
            'filters' => $request->only(['date_debut', 'date_fin', 'type', 'sexe', 'etablissement_id']),
            'etablissementsListe' => Etablissement::orderBy('nom')->get(),
            'total' => $total,
            'eleves' => $eleves,
            'garcons' => $garcons,
            'filles' => $filles,
            'moisLabels' => $mois->map(fn ($m) => ucfirst($m->translatedFormat('M'))),
            'evolutionMensuelle' => $evolutionMensuelle,
            'genreParMois' => $genreParMois,
            'repartitionType' => $repartitionType,
            'topEtablissements' => $topEtablissements,
            'resumeEtablissements' => $resumeEtablissements,
            'frequentationMensuelle' => $frequentationMensuelle,
        ]);
    }
}
