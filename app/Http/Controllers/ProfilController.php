<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfilController extends Controller
{
    public function edit(): View
    {
        return view('profil.edit', ['user' => Auth::user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', 'min:8'],
            'current_password' => ['required_with:password', 'nullable', 'current_password'],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return back()->with('success', 'Profil mis à jour avec succès.');
    }
}
