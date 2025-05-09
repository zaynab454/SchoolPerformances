<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NiveauScolaire;

class NiveauScolaireSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer les niveaux scolaires
        $niveaux = [
            ['code_niveau' => 'PRIM1', 'description' => 'Primaire 1ère année'],
            ['code_niveau' => 'PRIM2', 'description' => 'Primaire 2ème année'],
            ['code_niveau' => 'PRIM3', 'description' => 'Primaire 3ème année'],
            ['code_niveau' => 'PRIM4', 'description' => 'Primaire 4ème année'],
            ['code_niveau' => 'PRIM5', 'description' => 'Primaire 5ème année'],
            ['code_niveau' => 'PRIM6', 'description' => 'Primaire 6ème année'],
            ['code_niveau' => 'COLL1', 'description' => 'Collège 1ère année'],
            ['code_niveau' => 'COLL2', 'description' => 'Collège 2ème année'],
            ['code_niveau' => 'COLL3', 'description' => 'Collège 3ème année']
        ];

        foreach ($niveaux as $niveau) {
            try {
                NiveauScolaire::create($niveau);
                $this->command->info("Niveau créé avec succès : {$niveau['code_niveau']}");
            } catch (\Exception $e) {
                $this->command->error("Erreur lors de la création du niveau {$niveau['code_niveau']}: " . $e->getMessage());
            }
        }

        $this->command->info('Niveaux scolaires créés avec succès !');
    }
}