<?php

namespace App\Http\Controllers;

use App\Models\Etablissement;
use App\Models\ExportHistorique;
use Illuminate\View\View;

class ExportController extends Controller
{
    public function index(): View
    {
        return view('exports.index', [
            'etablissements' => Etablissement::orderBy('nom')->get(),
            'historique' => ExportHistorique::with('user')->latest('date_generation')->take(15)->get(),
        ]);
    }
}
