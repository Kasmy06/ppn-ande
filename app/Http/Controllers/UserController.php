<?php

namespace App\Http\Controllers;

use App\Models\JournalActivite;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('parametres.utilisateurs', [
            'users' => User::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:super_admin,agent'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'actif' => true,
        ]);

        JournalActivite::log('Création utilisateur', $user, $user->name);

        return redirect()->route('parametres.utilisateurs.index')->with('success', 'Utilisateur créé avec succès.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', 'in:super_admin,agent'],
            'actif' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        if ($user->id === Auth::id() && $data['role'] !== 'super_admin') {
            return back()->withErrors(['role' => 'Vous ne pouvez pas retirer vos propres droits de Super Admin.']);
        }

        if ($user->id === Auth::id() && ! $request->boolean('actif')) {
            return back()->withErrors(['actif' => 'Vous ne pouvez pas désactiver votre propre compte.']);
        }

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role = $data['role'];
        $user->actif = $request->boolean('actif');

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();
        JournalActivite::log('Modification utilisateur', $user, $user->name);

        return redirect()->route('parametres.utilisateurs.index')->with('success', 'Utilisateur modifié avec succès.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->withErrors(['user' => 'Vous ne pouvez pas supprimer votre propre compte.']);
        }

        $nom = $user->name;
        $user->delete();
        JournalActivite::log('Suppression utilisateur', null, $nom);

        return redirect()->route('parametres.utilisateurs.index')->with('success', 'Utilisateur supprimé.');
    }
}
