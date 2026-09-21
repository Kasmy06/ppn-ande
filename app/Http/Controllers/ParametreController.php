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
            'contact' => [
                'adresse' => SiteInfo::adresse(),
                'telephones' => implode("\n", SiteInfo::telephones()),
                'email' => SiteInfo::email(),
                'horaires' => SiteInfo::horaires(),
                'a_propos' => SiteInfo::aPropos(),
                'carte_url' => ParametreApplication::get('carte_url'),
            ],
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
            'carte_url' => ['nullable', 'url', 'max:1000', function ($attribute, $value, $fail) {
                if ($value && ! SiteInfo::carteValide($value)) {
                    $fail('Collez l\'adresse « src » du code d\'intégration Google Maps (https://www.google.com/maps/embed?...) ou OpenStreetMap.');
                }
            }],
        ]);

        foreach (['adresse', 'telephones', 'email', 'horaires', 'a_propos', 'carte_url'] as $cle) {
            ParametreApplication::set($cle, $data[$cle] ?? null);
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
