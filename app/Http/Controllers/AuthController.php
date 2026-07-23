<?php

namespace App\Http\Controllers;

use App\Models\JournalActivite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['auth' => 'Email ou mot de passe incorrect.']);
        }

        if (! $request->user()->actif) {
            Auth::logout();

            return back()->withErrors(['auth' => 'Ce compte a été désactivé. Contactez un administrateur.']);
        }

        $request->session()->regenerate();
        JournalActivite::log('Connexion', null, $request->user()->name);

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        if ($request->user()) {
            JournalActivite::log('Déconnexion', null, $request->user()->name);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
