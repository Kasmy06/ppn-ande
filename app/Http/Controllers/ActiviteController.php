<?php

namespace App\Http\Controllers;

use App\Models\Activite;
use App\Models\JournalActivite;
use App\Models\Media;
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
        return view('activites.form', ['activite' => new Activite(['publie' => false, 'categorie' => 'atelier'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $activite = Activite::create($this->validated($request));
        $this->alimenterGalerie($request, $activite);
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
        $activite->medias()->where('auto', true)->update(['publie' => $activite->publie]);
        $this->alimenterGalerie($request, $activite);
        JournalActivite::log('Modification activité', $activite, $activite->titre);

        return redirect()->route('activites.index')->with('success', 'Activité modifiée avec succès.');
    }

    /** Publie ou retire du site public en un clic (la copie de couverture dans la galerie suit). */
    public function basculerPublication(Activite $activite): RedirectResponse
    {
        $activite->update(['publie' => ! $activite->publie]);
        $activite->medias()->where('auto', true)->update(['publie' => $activite->publie]);
        JournalActivite::log($activite->publie ? 'Publication activité' : 'Retrait activité du site', $activite, $activite->titre);

        return back()->with('success', $activite->publie ? 'Activité publiée sur le site.' : 'Activité retirée du site (brouillon).');
    }

    public function destroy(Activite $activite): RedirectResponse
    {
        $titre = $activite->titre;
        if ($activite->image_path) {
            Storage::disk('public')->delete($activite->image_path);
        }
        // La copie de la couverture dans la galerie disparaît avec l'activité ; les autres médias restent.
        $this->supprimerCopiesAuto($activite);
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
            'image' => ['nullable', 'image', 'max:10240'],
            'photos' => ['nullable', 'array', 'max:20'],
            'photos.*' => ['image', 'max:10240'],
            'video' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm,video/quicktime', 'max:51200'],
            'video_url' => ['nullable', 'url', 'max:255'],
        ]);

        $data['publie'] = $request->boolean('publie');
        unset($data['image'], $data['photos'], $data['video'], $data['video_url']);

        if ($request->hasFile('image')) {
            if ($existante?->image_path) {
                Storage::disk('public')->delete($existante->image_path);
            }
            $data['image_path'] = $request->file('image')->store('activites', 'public');
        }

        return $data;
    }

    /** Les photos et vidéos ajoutées à une activité apparaissent aussi dans la galerie publique. */
    private function alimenterGalerie(Request $request, Activite $activite): void
    {
        if ($request->hasFile('image') && $activite->image_path) {
            // Nouvelle couverture : elle remplace l'ancienne copie au lieu de s'ajouter à la galerie.
            $this->supprimerCopiesAuto($activite);
            $copie = 'medias/'.basename($activite->image_path);
            Storage::disk('public')->copy($activite->image_path, $copie);
            Media::create([
                'titre' => $activite->titre,
                'type' => 'photo',
                'fichier_path' => $copie,
                'activite_id' => $activite->id,
                'publie' => $activite->publie,
                'auto' => true,
            ]);
        }

        foreach ($request->file('photos', []) as $i => $photo) {
            Media::create([
                'titre' => $activite->titre.' – photo '.($i + 1),
                'type' => 'photo',
                'fichier_path' => $photo->store('medias', 'public'),
                'activite_id' => $activite->id,
                'publie' => $activite->publie,
            ]);
        }

        if ($request->hasFile('video') || $request->filled('video_url')) {
            Media::create([
                'titre' => $activite->titre.' (vidéo)',
                'type' => 'video',
                'fichier_path' => $request->hasFile('video') ? $request->file('video')->store('medias', 'public') : null,
                'video_url' => $request->hasFile('video') ? null : $request->input('video_url'),
                'activite_id' => $activite->id,
                'publie' => $activite->publie,
            ]);
        }
    }

    private function supprimerCopiesAuto(Activite $activite): void
    {
        foreach ($activite->medias()->where('auto', true)->get() as $copie) {
            if ($copie->fichier_path) {
                Storage::disk('public')->delete($copie->fichier_path);
            }
            $copie->delete();
        }
    }
}
