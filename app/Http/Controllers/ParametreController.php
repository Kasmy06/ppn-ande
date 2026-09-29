<?php

namespace App\Http\Controllers;

use App\Models\JournalActivite;
use App\Models\ParametreApplication;
use App\Support\SiteInfo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ParametreController extends Controller
{
    public function edit(): View
    {
        $logoPath = ParametreApplication::get('logo_path');

        return view('parametres.application', [
            'nomStructure' => ParametreApplication::get('nom_structure', "PPN d'Andé"),
            'capaciteJournaliere' => ParametreApplication::get('capacite_journaliere', 60),
            'logoUrl' => $logoPath ? Storage::disk('public')->url($logoPath) : null,
            // Valeurs brutes (même si masquées au public) pour ne jamais les perdre à l'enregistrement.
            'contact' => [
                'adresse' => ParametreApplication::get('adresse'),
                'telephones' => ParametreApplication::get('telephones'),
                'email' => ParametreApplication::get('email'),
                'horaires' => ParametreApplication::get('horaires'),
                'a_propos' => ParametreApplication::get('a_propos'),
                'carte_url' => ParametreApplication::get('carte_url'),
                'responsable' => ParametreApplication::get('responsable'),
                'hebergeur' => ParametreApplication::get('hebergeur'),
                'duree_conservation' => ParametreApplication::get('duree_conservation'),
                'mentions_legales' => ParametreApplication::get('mentions_legales'),
                'confidentialite' => ParametreApplication::get('confidentialite'),
                'reseau_texte' => SiteInfo::reseauTexte(),
                'reseau_villes_urbaines' => SiteInfo::reseauVillesUrbaines(),
                'reseau_villes_rurales' => SiteInfo::reseauVillesRurales(),
            ],
            'visibilite' => collect(array_keys(SiteInfo::INFOS))
                ->mapWithKeys(fn ($cle) => [$cle => SiteInfo::visible($cle)])
                ->all(),
            'pagesActives' => collect(array_keys(SiteInfo::PAGES))
                ->mapWithKeys(fn ($cle) => [$cle => SiteInfo::page($cle)])
                ->all(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nom_structure' => ['required', 'string', 'max:150'],
            'capacite_journaliere' => ['required', 'integer', 'min:1', 'max:1000'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,svg,webp', 'max:2048'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'telephones' => ['nullable', 'string', 'max:500'],
            'email' => ['nullable', 'email', 'max:150'],
            'horaires' => ['nullable', 'string', 'max:500'],
            'a_propos' => ['nullable', 'string', 'max:5000'],
            'responsable' => ['nullable', 'string', 'max:200'],
            'hebergeur' => ['nullable', 'string', 'max:300'],
            'duree_conservation' => ['nullable', 'string', 'max:60'],
            'mentions_legales' => ['nullable', 'string', 'max:10000'],
            'confidentialite' => ['nullable', 'string', 'max:10000'],
            'reseau_texte' => ['nullable', 'string', 'max:3000'],
            'reseau_villes_urbaines' => ['nullable', 'string', 'max:1000'],
            'reseau_villes_rurales' => ['nullable', 'string', 'max:1000'],
            'carte_url' => ['nullable', 'url', 'max:1000', function ($attribute, $value, $fail) {
                if ($value && ! SiteInfo::carteValide($value)) {
                    $fail('Collez l\'adresse « src » du code d\'intégration Google Maps (https://www.google.com/maps/embed?...) ou OpenStreetMap.');
                }
            }],
        ]);

        foreach (['adresse', 'telephones', 'email', 'horaires', 'a_propos', 'carte_url', 'responsable', 'hebergeur', 'duree_conservation', 'mentions_legales', 'confidentialite', 'reseau_texte', 'reseau_villes_urbaines', 'reseau_villes_rurales'] as $cle) {
            ParametreApplication::set($cle, $data[$cle] ?? null);
        }

        // Interrupteurs de publication : envoyés par le formulaire (champ caché « 0 » + case « 1 »).
        foreach (array_keys(SiteInfo::INFOS) as $cle) {
            if ($request->has('visible_'.$cle)) {
                ParametreApplication::set('visible_'.$cle, $request->boolean('visible_'.$cle) ? '1' : '0');
            }
        }
        foreach (array_keys(SiteInfo::PAGES) as $cle) {
            if ($request->has('visible_page_'.$cle)) {
                ParametreApplication::set('visible_page_'.$cle, $request->boolean('visible_page_'.$cle) ? '1' : '0');
            }
        }

        ParametreApplication::set('nom_structure', $data['nom_structure']);
        ParametreApplication::set('capacite_journaliere', $data['capacite_journaliere']);

        if ($request->hasFile('logo')) {
            $ancien = ParametreApplication::get('logo_path');
            $chemin = $request->file('logo')->store('logos', 'public');
            ParametreApplication::set('logo_path', $chemin);

            if ($ancien) {
                Storage::disk('public')->delete($ancien);
            }
        }

        JournalActivite::log('Modification paramètres application');

        return back()->with('success', 'Paramètres mis à jour avec succès.');
    }

    public function supprimerLogo(): RedirectResponse
    {
        $ancien = ParametreApplication::get('logo_path');

        if ($ancien) {
            Storage::disk('public')->delete($ancien);
            ParametreApplication::set('logo_path', null);
        }

        JournalActivite::log('Suppression du logo de l\'application');

        return back()->with('success', 'Logo réinitialisé.');
    }
}
