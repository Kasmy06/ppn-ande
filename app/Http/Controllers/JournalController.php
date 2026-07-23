<?php

namespace App\Http\Controllers;

use App\Models\JournalActivite;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JournalController extends Controller
{
    public function index(Request $request): View
    {
        $journal = JournalActivite::with('user')
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->query('user_id')))
            ->when($request->filled('action'), fn ($q) => $q->where('action', 'like', '%'.$request->query('action').'%'))
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('parametres.journal', [
            'journal' => $journal,
            'users' => User::orderBy('name')->get(),
            'filters' => $request->only(['user_id', 'action']),
        ]);
    }
}
