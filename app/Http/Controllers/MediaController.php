<?php

namespace App\Http\Controllers;

use App\Models\Activite;
use App\Models\JournalActivite;
use App\Models\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function index(): View
    {
        return view('medias.index', ['medias' => Media::with('activite')->latest()->paginate(12)]);
    }

    public function create(): View
    {
        return view('medias.form', ['media' => new Media(['type' => 'photo', 'publie' => false]), 'activites' => $this->activites()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $photos = $request->file('photos', []);

        if ($request->input('type') === 'photo' && count($photos) > 1) {
            return $this->storeLot($request, $photos);
        }

        $media = Media::create($this->validated($request));
        JournalActivite::log('Ajout média', $media, $media->titre);

        return redirect()->route('medias.index')->with('success', 'Média ajouté avec succès.');
    }

    /** Envoi de plusieurs photos d'un coup : une entrée par fichier, titre numéroté. */
    private function storeLot(Request $request, array $photos): RedirectResponse
    {
        $request->validate([
            'titre' => ['required', 'string', 'max:150'],
            'legende' => ['nullable', 'string', 'max:1000'],
            'activite_id' => ['nullable', 'exists:activites,id'],
            'photos' => ['array', 'max:20'],
            'photos.*' => ['image', 'max:10240'],
        ]);

        foreach (array_values($photos) as $i => $fichier) {
            $media = Media::create([
                'titre' => $request->input('titre').' '.($i + 1),
                'type' => 'photo',
                'legende' => $request->input('legende'),
                'activite_id' => $request->input('activite_id') ?: null,
                'publie' => $this->peutPublier($request) && $request->boolean('publie'),
                'fichier_path' => $fichier->store('medias', 'public'),
            ]);
            JournalActivite::log('Ajout média', $media, $media->titre);
        }

        return redirect()->route('medias.index')->with('success', count($photos).' photos ajoutées avec succès.');
    }

    public function edit(Media $media): View
    {
        return view('medias.form', ['media' => $media, 'activites' => $this->activites()]);
    }

    public function update(Request $request, Media $media): RedirectResponse
    {
        $media->update($this->validated($request, $media));
        JournalActivite::log('Modification média', $media, $media->titre);

        return redirect()->route('medias.index')->with('success', 'Média modifié avec succès.');
    }

    public function destroy(Media $media): RedirectResponse
    {
        $titre = $media->titre;
        if ($media->fichier_path) {
            Storage::disk('public')->delete($media->fichier_path);
        }
        $media->delete();
        JournalActivite::log('Suppression média', null, $titre);

        return redirect()->route('medias.index')->with('success', 'Média supprimé.');
    }

    /** Seul le Super Admin décide de ce qui devient public ; les ajouts des agents restent en brouillon. */
    private function peutPublier(Request $request): bool
    {
        return (bool) $request->user()?->isSuperAdmin();
    }

    public function basculerPublication(Media $media): RedirectResponse
    {
        $media->update(['publie' => ! $media->publie]);
        JournalActivite::log($media->publie ? 'Publication média' : 'Retrait média du site', $media, $media->titre);

        return back()->with('success', $media->publie ? 'Média publié dans la galerie.' : 'Média retiré de la galerie (brouillon).');
    }

    private function activites()
    {
        return Activite::orderByDesc('date_debut')->get(['id', 'titre']);
    }

    private function validated(Request $request, ?Media $existant = null): array
    {
        $data = $request->validate([
            'titre' => ['required', 'string', 'max:150'],
            'type' => ['required', 'in:photo,video'],
            'legende' => ['nullable', 'string', 'max:1000'],
            'activite_id' => ['nullable', 'exists:activites,id'],
            'photos' => ['nullable', 'array', 'max:20'],
            'photos.*' => ['image', 'max:10240'],
            'video' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm,video/quicktime', 'max:51200'],
            'video_url' => ['nullable', 'url', 'max:255'],
        ]);

        $type = $data['type'];
        $fichier = $type === 'photo' ? ($request->file('photos')[0] ?? null) : $request->file('video');
        $aFichierExistant = $existant && $existant->type === $type && $existant->fichier_path;

        if ($type === 'photo' && ! $fichier && ! $aFichierExistant) {
            throw ValidationException::withMessages(['photos' => 'Choisissez une photo.']);
        }
        if ($type === 'video' && ! $fichier && empty($data['video_url']) && ! $aFichierExistant) {
            throw ValidationException::withMessages(['video' => 'Téléversez une vidéo ou indiquez un lien YouTube/Vimeo.']);
        }

        $sortie = [
            'titre' => $data['titre'],
            'type' => $type,
            'legende' => $data['legende'] ?? null,
            'activite_id' => $data['activite_id'] ?? null,
            'publie' => $this->peutPublier($request) && $request->boolean('publie'),
            'video_url' => $type === 'video' ? ($data['video_url'] ?? null) : null,
        ];

        if ($fichier) {
            if ($existant?->fichier_path) {
                Storage::disk('public')->delete($existant->fichier_path);
            }
            $sortie['fichier_path'] = $fichier->store('medias', 'public');
        } elseif ($existant && $existant->type !== $type && $existant->fichier_path) {
            Storage::disk('public')->delete($existant->fichier_path);
            $sortie['fichier_path'] = null;
        }

        return $sortie;
    }
}
