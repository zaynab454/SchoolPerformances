<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        $this->call([
            AdminSeeder::class,
            ProvinceSeeder::class,
            CommuneSeeder::class,
            EtablissementSeeder::class,
            NiveauScolaireSeeder::class,
            MatiereSeeder::class,
            AnneeScolaireSeeder::class,
            EleveSeeder::class,
            ResultatEleveSeeder::class
        ]);
    }
}