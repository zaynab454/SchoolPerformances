<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AnneeScolaire;

class AnneeScolaireSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer l'année scolaire active
        $anneeActive = AnneeScolaire::create([
            'annee_scolaire' => '2025-2026',
            'est_courante' => true
        ]);

        if ($anneeActive) {
            $this->command->info("Année scolaire créée avec succès : 2025-2026 (active)");
        }

        // Créer une année scolaire précédente
        $anneePrecedente = AnneeScolaire::create([
            'annee_scolaire' => '2024-2025',
            'est_courante' => false
        ]);

        if ($anneePrecedente) {
            $this->command->info("Année scolaire créée avec succès : 2024-2025");
        }
    }
}
