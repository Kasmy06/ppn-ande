<?php

namespace App\Http\Controllers;

use App\Models\Etablissement;
use App\Models\JournalActivite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EtablissementController extends Controller
{
    public function index(Request $request): View
    {
        $etablissements = Etablissement::withCount('visiteurs')
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->query('q').'%';
                $q->where(function ($q) use ($term) {
                    $q->where('nom', 'like', $term)->orWhere('ville', 'like', $term);
                });
            })
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->query('type')))
            ->orderBy('nom')
            ->paginate(10)
            ->withQueryString();

        return view('etablissements.index', [
            'etablissements' => $etablissements,
            'filters' => $request->only(['q', 'type']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $etablissement = Etablissement::create($this->validated($request));
        JournalActivite::log('Ajout établissement', $etablissement, $etablissement->nom);

        return redirect()->route('etablissements.index')->with('success', 'Établissement ajouté avec succès.');
    }

    public function update(Request $request, Etablissement $etablissement): RedirectResponse
    {
        $etablissement->update($this->validated($request, $etablissement->id));
        JournalActivite::log('Modification établissement', $etablissement, $etablissement->nom);

        return redirect()->route('etablissements.index')->with('success', 'Établissement modifié avec succès.');
    }

    public function destroy(Etablissement $etablissement): RedirectResponse
    {
        $nom = $etablissement->nom;
        $etablissement->delete();
        JournalActivite::log('Suppression établissement', null, $nom);

        return redirect()->route('etablissements.index')->with('success', 'Établissement supprimé.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'nom' => ['required', 'string', 'max:150', 'unique:etablissements,nom'.($ignoreId ? ",{$ignoreId}" : '')],
            'type' => ['required', 'in:scolaire,collectivite,association,autre'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'ville' => ['nullable', 'string', 'max:100'],
            'code_postal' => ['nullable', 'string', 'max:10'],
            'contact_nom' => ['nullable', 'string', 'max:150'],
            'contact_telephone' => ['nullable', 'string', 'max:30'],
            'contact_email' => ['nullable', 'email', 'max:150'],
        ]);
    }
}
