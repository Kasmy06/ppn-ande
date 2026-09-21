<?php

namespace App\Http\Controllers;

use App\Models\Activite;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function accueil(): View
    {
        return view('site.accueil', [
            'prochaines' => Activite::publie()->aVenir()->orderBy('date_debut')->limit(3)->get(),
            'recentes' => Activite::publie()->orderByDesc('date_debut')->limit(3)->get(),
            'medias' => Media::publie()->latest()->limit(6)->get(),
        ]);
    }

    public function activites(Request $request): View
    {
        $categorie = $request->query('categorie');
        $periode = $request->query('periode') === 'passees' ? 'passees' : 'a_venir';

        $activites = Activite::publie()
            ->when(array_key_exists((string) $categorie, Activite::CATEGORIES), fn ($q) => $q->where('categorie', $categorie))
            ->when(
                $periode === 'a_venir',
                fn ($q) => $q->aVenir()->orderBy('date_debut'),
                fn ($q) => $q->whereRaw('COALESCE(date_fin, date_debut) < ?', [today()->toDateString()])->orderByDesc('date_debut'),
            )
            ->paginate(9)
            ->withQueryString();

        return view('site.activites', compact('activites', 'categorie', 'periode'));
    }

    public function galerie(Request $request): View
    {
        $type = in_array($request->query('type'), ['photo', 'video'], true) ? $request->query('type') : null;

        $medias = Media::publie()
            ->when($type, fn ($q) => $q->where('type', $type))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('site.galerie', compact('medias', 'type'));
    }

    public function activite(Activite $activite): View
    {
        abort_unless($activite->publie, 404);

        return view('site.activite', [
            'activite' => $activite,
            'medias' => $activite->medias()->publie()->latest()->get(),
            'similaires' => Activite::publie()->where('categorie', $activite->categorie)
                ->whereKeyNot($activite->id)->orderByDesc('date_debut')->limit(3)->get(),
        ]);
    }
}
