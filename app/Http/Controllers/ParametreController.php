<?php

namespace App\Http\Controllers;

use App\Models\JournalActivite;
use App\Models\ParametreApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParametreController extends Controller
{
    public function edit(): View
    {
        return view('parametres.application', [
            'nomStructure' => ParametreApplication::get('nom_structure', "PPN d'Andé"),
            'capaciteJournaliere' => ParametreApplication::get('capacite_journaliere', 60),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nom_structure' => ['required', 'string', 'max:150'],
            'capacite_journaliere' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        ParametreApplication::set('nom_structure', $data['nom_structure']);
        ParametreApplication::set('capacite_journaliere', $data['capacite_journaliere']);

        JournalActivite::log('Modification paramètres application');

        return back()->with('success', 'Paramètres mis à jour avec succès.');
    }
}
