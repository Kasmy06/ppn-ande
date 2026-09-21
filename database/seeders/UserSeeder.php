<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Crée les comptes initiaux. Aucun mot de passe par défaut : il vient de SEED_ADMIN_PASSWORD /
     * SEED_AGENT_PASSWORD, sinon il est généré aléatoirement et affiché une seule fois.
     */
    public function run(): void
    {
        $this->creer('admin@ppn-ande.fr', 'Administrateur PPN', 'super_admin', env('SEED_ADMIN_PASSWORD'));
        $this->creer('agent@ppn-ande.fr', 'Agent Accueil', 'agent', env('SEED_AGENT_PASSWORD'));
    }

    private function creer(string $email, string $nom, string $role, ?string $motDePasse): void
    {
        $genere = $motDePasse ? null : Str::password(16, symbols: false);

        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => $nom, 'password' => $motDePasse ?: $genere, 'role' => $role, 'actif' => true]
        );

        if ($user->wasRecentlyCreated && $genere && $this->command) {
            $this->command->warn("Compte {$email} créé — mot de passe (à noter, affiché une seule fois) : {$genere}");
        }
    }
}
