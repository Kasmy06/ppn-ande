<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Visiteur;
use Illuminate\Database\Seeder;

class VisiteurSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userIds = User::pluck('id');

        Visiteur::factory()
            ->count(280)
            ->state(fn () => ['cree_par' => $userIds->random()])
            ->create();
    }
}
