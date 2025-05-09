<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ResultatEleve;
use App\Models\AnneeScolaire;

class ResultatEleveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer l'année scolaire active
        $anneeScolaire = AnneeScolaire::where('est_courante', true)->first();
        if (!$anneeScolaire) {
            $this->command->error('Aucune année scolaire active trouvée. Veuillez d\'abord créer une année scolaire.');
            return;
        }

        // Créer des résultats pour chaque élève dans chaque matière
        $resultats = [
            [
                'code_eleve' => 'ELEVE127680001',
                'id_matiere' => 1,
                'annee_scolaire' => '2025-2026',
                'session' => '1ère session',
                'MoyenNoteCC' => 15,
                'MoyenExamenNote' => 18,
                'MoyenCC' => 16,
                'MoyenExam' => 17,
                'MoyenSession' => 16.5
            ],
            [
                'code_eleve' => 'ELEVE127680002',
                'id_matiere' => 1,
                'annee_scolaire' => '2025-2026',
                'session' => '1ère session',
                'MoyenNoteCC' => 14,
                'MoyenExamenNote' => 16,
                'MoyenCC' => 15,
                'MoyenExam' => 16,
                'MoyenSession' => 15.5
            ],
            // Ajoutez d'autres résultats ici
        ];

        foreach ($resultats as $resultat) {
            try {
                ResultatEleve::create($resultat);
                $this->command->info("Résultat créé avec succès pour l'élève {$resultat['code_eleve']} et la matière {$resultat['id_matiere']}");
            } catch (\Exception $e) {
                $this->command->error("Erreur lors de la création du résultat : " . $e->getMessage());
            }
        }

        $this->command->info('Résultats des élèves créés avec succès !');
    }
} 