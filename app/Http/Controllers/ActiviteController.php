<?php

namespace App\Http\Controllers;

use App\Models\Activite;
use App\Models\JournalActivite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ActiviteController extends Controller
{
    public function index(): View
    {
        return view('activites.index', [
            'activites' => Activite::orderByDesc('date_debut')->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('activites.form', ['activite' => new Activite(['publie' => true, 'categorie' => 'atelier'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $activite = Activite::create($this->validated($request));
        JournalActivite::log('Ajout activité', $activite, $activite->titre);

        return redirect()->route('activites.index')->with('success', 'Activité ajoutée avec succès.');
    }

    public function edit(Activite $activite): View
    {
        return view('activites.form', compact('activite'));
    }

    public function update(Request $request, Activite $activite): RedirectResponse
    {
        $activite->update($this->validated($request, $activite));
        JournalActivite::log('Modification activité', $activite, $activite->titre);

        return redirect()->route('activites.index')->with('success', 'Activité modifiée avec succès.');
    }

    public function destroy(Activite $activite): RedirectResponse
    {
        $titre = $activite->titre;
        if ($activite->image_path) {
            Storage::disk('public')->delete($activite->image_path);
        }
        $activite->delete();
        JournalActivite::log('Suppression activité', null, $titre);

        return redirect()->route('activites.index')->with('success', 'Activité supprimée.');
    }

    private function validated(Request $request, ?Activite $existante = null): array
    {
        $data = $request->validate([
            'titre' => ['required', 'string', 'max:150'],
            'categorie' => ['required', 'in:'.implode(',', array_keys(Activite::CATEGORIES))],
            'description' => ['required', 'string', 'max:5000'],
            'date_debut' => ['required', 'date'],
            'date_fin' => ['nullable', 'date', 'after_or_equal:date_debut'],
            'horaires' => ['nullable', 'string', 'max:60'],
            'lieu' => ['nullable', 'string', 'max:150'],
            'image' => ['nullable', 'image', 'max:3072'],
        ]);

        $data['publie'] = $request->boolean('publie');
        unset($data['image']);

        if ($request->hasFile('image')) {
            if ($existante?->image_path) {
                Storage::disk('public')->delete($existante->image_path);
            }
            $data['image_path'] = $request->file('image')->store('activites', 'public');
        }

        return $data;
    }
}
