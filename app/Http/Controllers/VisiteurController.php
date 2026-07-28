<?php

namespace App\Http\Controllers;

use App\Exports\VisiteursExport;
use App\Models\Etablissement;
use App\Models\ExportHistorique;
use App\Models\JournalActivite;
use App\Models\Visiteur;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class VisiteurController extends Controller
{
    private const SORTABLE = ['nom', 'sexe', 'type', 'date_visite'];

    public function index(Request $request): View
    {
        $visiteurs = $this->filteredQuery($request)
            ->with('etablissement')
            ->paginate(10)
            ->withQueryString();

        return view('visiteurs.index', [
            'visiteurs' => $visiteurs,
            'etablissements' => Etablissement::orderBy('nom')->get(),
            'filters' => $request->only(['q', 'type', 'sexe', 'date', 'sort', 'dir']),
        ]);
    }

    public function rechercherExistant(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $resultats = Visiteur::query()
            ->select([
                'visiteurs.prenom',
                'visiteurs.nom',
                'visiteurs.sexe',
                'visiteurs.type',
                'visiteurs.etablissement_id',
                'visiteurs.classe_ou_poste',
                'etablissements.nom as etablissement_nom',
                DB::raw('COUNT(*) as nb_visites'),
                DB::raw('MAX(visiteurs.date_visite) as derniere_visite'),
                DB::raw('MAX(visiteurs.telephone) as telephone'),
                DB::raw('MAX(visiteurs.email) as email'),
            ])
            ->leftJoin('etablissements', 'etablissements.id', '=', 'visiteurs.etablissement_id')
            ->where(function ($query) use ($q) {
                $term = "%{$q}%";
                $query->where('visiteurs.prenom', 'like', $term)
                    ->orWhere('visiteurs.nom', 'like', $term)
                    ->orWhereRaw("CONCAT(visiteurs.prenom, ' ', visiteurs.nom) LIKE ?", [$term]);
            })
            ->groupBy(
                'visiteurs.prenom',
                'visiteurs.nom',
                'visiteurs.sexe',
                'visiteurs.type',
                'visiteurs.etablissement_id',
                'visiteurs.classe_ou_poste',
                'etablissements.nom'
            )
            ->orderByDesc('derniere_visite')
            ->limit(8)
            ->get();

        return response()->json($resultats);
    }

    public function fiche(Request $request): View
    {
        $request->validate([
            'prenom' => ['required', 'string'],
            'nom' => ['required', 'string'],
            'sexe' => ['required', 'in:M,F'],
        ]);

        $visites = Visiteur::query()
            ->where('prenom', $request->query('prenom'))
            ->where('nom', $request->query('nom'))
            ->where('sexe', $request->query('sexe'))
            ->with('etablissement')
            ->orderByDesc('date_visite')
            ->orderByDesc('heure_arrivee')
            ->get();

        abort_if($visites->isEmpty(), 404);

        return view('visiteurs.fiche', [
            'prenom' => $request->query('prenom'),
            'nom' => $request->query('nom'),
            'sexe' => $request->query('sexe'),
            'visites' => $visites,
            'derniereVisite' => $visites->first(),
            'premiereVisite' => $visites->last(),
            'etablissements' => $visites->pluck('etablissement.nom')->filter()->unique()->values(),
            'types' => $visites->pluck('type')->unique()->values(),
            'telephone' => $visites->pluck('telephone')->filter()->first(),
            'email' => $visites->pluck('email')->filter()->first(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['cree_par'] = Auth::id();

        $visiteur = Visiteur::create($data);
        JournalActivite::log('Ajout visiteur', $visiteur, "{$visiteur->prenom} {$visiteur->nom}");

        return redirect()->route('visiteurs.index')->with('success', 'Visiteur ajouté avec succès.');
    }

    public function update(Request $request, Visiteur $visiteur): RedirectResponse
    {
        $visiteur->update($this->validated($request));
        JournalActivite::log('Modification visiteur', $visiteur, "{$visiteur->prenom} {$visiteur->nom}");

        return redirect()->route('visiteurs.index')->with('success', 'Visiteur modifié avec succès.');
    }

    public function destroy(Visiteur $visiteur): RedirectResponse
    {
        $nom = "{$visiteur->prenom} {$visiteur->nom}";
        $visiteur->delete();
        JournalActivite::log('Suppression visiteur', null, $nom);

        return redirect()->route('visiteurs.index')->with('success', 'Visiteur supprimé.');
    }

    public function export(Request $request): Response
    {
        $format = in_array($request->query('format'), ['csv', 'xlsx', 'pdf'], true) ? $request->query('format') : 'csv';
        $visiteurs = $this->filteredQuery($request)->with('etablissement')->get();

        ExportHistorique::enregistrer('visiteurs', $format, $request->only(['q', 'type', 'sexe', 'date']));
        JournalActivite::log('Export visiteurs', null, strtoupper($format).' — '.$visiteurs->count().' entrée(s)');

        $basename = 'visiteurs_ppn_ande_'.now()->format('Y-m-d');

        return match ($format) {
            'xlsx' => Excel::download(new VisiteursExport($visiteurs), "{$basename}.xlsx"),
            'pdf' => Pdf::loadView('exports.visiteurs-pdf', ['visiteurs' => $visiteurs])->download("{$basename}.pdf"),
            default => response()->streamDownload(function () use ($visiteurs) {
                $handle = fopen('php://output', 'w');
                fwrite($handle, "\xEF\xBB\xBF");
                fputcsv($handle, ['Prénom', 'Nom', 'Genre', 'Type', 'Établissement', 'Classe / Poste', 'Téléphone', 'Email', 'Date de visite', 'Heure d\'arrivée'], ';');

                foreach ($visiteurs as $v) {
                    fputcsv($handle, [
                        $v->prenom,
                        $v->nom,
                        $v->sexe === 'M' ? 'Masculin' : 'Féminin',
                        $v->type,
                        $v->etablissement->nom ?? '',
                        $v->classe_ou_poste,
                        $v->telephone,
                        $v->email,
                        $v->date_visite->format('d/m/Y'),
                        $v->heureFormatee ?? '',
                    ], ';');
                }

                fclose($handle);
            }, "{$basename}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']),
        };
    }

    private function filteredQuery(Request $request)
    {
        $sort = in_array($request->query('sort'), self::SORTABLE, true) ? $request->query('sort') : 'date_visite';
        $dir = $request->query('dir') === 'asc' ? 'asc' : 'desc';

        $query = Visiteur::query()
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->query('q').'%';
                $q->where(function ($q) use ($term) {
                    $q->where('prenom', 'like', $term)
                        ->orWhere('nom', 'like', $term)
                        ->orWhereHas('etablissement', fn ($q) => $q->where('nom', 'like', $term));
                });
            })
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->query('type')))
            ->when($request->filled('sexe'), fn ($q) => $q->where('sexe', $request->query('sexe')))
            ->when($request->filled('date'), fn ($q) => $q->whereDate('date_visite', $request->query('date')));

        if ($sort === 'nom') {
            $query->orderBy('prenom', $dir)->orderBy('nom', $dir);
        } else {
            $query->orderBy($sort, $dir);
        }

        return $query;
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'prenom' => ['required', 'string', 'max:100'],
            'nom' => ['required', 'string', 'max:100'],
            'sexe' => ['required', 'in:M,F'],
            'type' => ['required', 'in:Élève,Fonctionnaire,Externe'],
            'etablissement_id' => ['nullable', 'exists:etablissements,id'],
            'classe_ou_poste' => ['nullable', 'string', 'max:100'],
            'telephone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'date_visite' => ['required', 'date'],
            'heure_arrivee' => ['required', 'date_format:H:i'],
        ]);
    }
}
