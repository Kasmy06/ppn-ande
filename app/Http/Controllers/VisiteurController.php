<?php

namespace App\Http\Controllers;

use App\Exports\VisiteursExport;
use App\Models\Etablissement;
use App\Models\ExportHistorique;
use App\Models\JournalActivite;
use App\Models\Visiteur;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
                fputcsv($handle, ['Prénom', 'Nom', 'Genre', 'Type', 'Établissement', 'Classe / Poste', 'Date de visite'], ';');

                foreach ($visiteurs as $v) {
                    fputcsv($handle, [
                        $v->prenom,
                        $v->nom,
                        $v->sexe === 'M' ? 'Masculin' : 'Féminin',
                        $v->type,
                        $v->etablissement->nom ?? '',
                        $v->classe_ou_poste,
                        $v->date_visite->format('d/m/Y'),
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
            'date_visite' => ['required', 'date'],
        ]);
    }
}
