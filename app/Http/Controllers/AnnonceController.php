<?php

namespace App\Http\Controllers;

use App\Models\Annonce;
use App\Models\JournalActivite;
use App\Support\ImageOptimizer;
use App\Support\Notifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AnnonceController extends Controller
{
    public function index(): View
    {
        return view('annonces.index', [
            'annonces' => Annonce::orderByDesc('date_publication')->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('annonces.form', ['annonce' => new Annonce(['publie' => false, 'date_publication' => today()])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $annonce = Annonce::create($this->validated($request) + ['a_valider' => ! $this->estAdmin($request)]);
        JournalActivite::log('Ajout annonce', $annonce, $annonce->titre);
        if ($annonce->a_valider) {
            Notifier::superAdmins('Nouvelle annonce à valider', "{$request->user()->name} a ajouté l'annonce « {$annonce->titre} » (brouillon). Elle sera visible du public une fois publiée par un Super Admin.");
        }

        return redirect()->route('annonces.index')->with('success', 'Annonce ajoutée avec succès.');
    }

    public function edit(Annonce $annonce): View
    {
        return view('annonces.form', compact('annonce'));
    }

    public function update(Request $request, Annonce $annonce): RedirectResponse
    {
        $annonce->update($this->validated($request, $annonce) + ['a_valider' => false]);
        JournalActivite::log('Modification annonce', $annonce, $annonce->titre);

        return redirect()->route('annonces.index')->with('success', 'Annonce modifiée avec succès.');
    }

    public function basculerPublication(Annonce $annonce): RedirectResponse
    {
        $annonce->update(['publie' => ! $annonce->publie, 'a_valider' => false]);
        JournalActivite::log($annonce->publie ? 'Publication annonce' : 'Retrait annonce du site', $annonce, $annonce->titre);

        return back()->with('success', $annonce->publie ? 'Annonce publiée sur le site.' : 'Annonce retirée du site (brouillon).');
    }

    public function destroy(Annonce $annonce): RedirectResponse
    {
        $titre = $annonce->titre;
        if ($annonce->image_path) {
            Storage::disk('public')->delete($annonce->image_path);
        }
        $annonce->delete();
        JournalActivite::log('Suppression annonce', null, $titre);

        return redirect()->route('annonces.index')->with('success', 'Annonce supprimée.');
    }

    private function validated(Request $request, ?Annonce $existante = null): array
    {
        $data = $request->validate([
            'titre' => ['required', 'string', 'max:150'],
            'contenu' => ['required', 'string', 'max:3000'],
            'urgente' => ['nullable', 'boolean'],
            'date_publication' => ['required', 'date'],
            'date_expiration' => ['nullable', 'date', 'after_or_equal:date_publication'],
            'image' => ['nullable', 'image', 'max:10240'],
        ]);

        $data['urgente'] = $request->boolean('urgente');
        // Seul le Super Admin décide de ce qui devient public ; les ajouts des agents restent en brouillon.
        $data['publie'] = (bool) $request->user()?->isSuperAdmin() && $request->boolean('publie');
        unset($data['image']);

        if ($request->hasFile('image')) {
            if ($existante?->image_path) {
                Storage::disk('public')->delete($existante->image_path);
            }
            $data['image_path'] = ImageOptimizer::stocker($request->file('image'), 'annonces');
        }

        return $data;
    }

    private function estAdmin(Request $request): bool
    {
        return (bool) $request->user()?->isSuperAdmin();
    }
}
