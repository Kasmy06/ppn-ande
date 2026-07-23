<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@ppn-ande.fr'],
            [
                'name' => 'Administrateur PPN',
                'password' => 'password',
                'role' => 'super_admin',
                'actif' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'agent@ppn-ande.fr'],
            [
                'name' => 'Agent Accueil',
                'password' => 'password',
                'role' => 'agent',
                'actif' => true,
            ]
        );
    }
}
