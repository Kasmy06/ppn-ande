<?php

namespace App\Http\Controllers;

use App\Models\JournalActivite;
use App\Models\MessageContact;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function index(): View
    {
        return view('messages.index', [
            'messages' => MessageContact::orderBy('lu')->latest()->paginate(15),
            'nonLus' => MessageContact::where('lu', false)->count(),
        ]);
    }

    public function basculerLu(MessageContact $message): RedirectResponse
    {
        $message->update(['lu' => ! $message->lu]);

        return back();
    }

    public function destroy(MessageContact $message): RedirectResponse
    {
        JournalActivite::log('Suppression message de contact', null, $message->sujet);
        $message->delete();

        return back()->with('success', 'Message supprimé.');
    }
}
