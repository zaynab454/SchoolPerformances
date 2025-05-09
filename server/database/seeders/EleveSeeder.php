<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Eleve;
use App\Models\Etablissement;
use App\Models\NiveauScolaire;

class EleveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Vérifier si l'établissement existe
        $etablissement = Etablissement::where('code_etab', '26258R')->first();
        if (!$etablissement) {
            $this->command->error("L'établissement 26258R n'existe pas dans la base de données.");
            return;
        }

        // Données des élèves pour l'établissement 12768 (E EL KODS)
        $eleves = [
            [
                'code_eleve' => 'ELEVE127680001',
                'nom_eleve_ar' => 'طالب 1',
                'prenom_eleve_ar' => 'اسم 1',
                'code_etab' => '26258R',
                'code_niveau' => 'PRIM1'
            ],
            [
                'code_eleve' => 'ELEVE127680002',
                'nom_eleve_ar' => 'طالب 2',
                'prenom_eleve_ar' => 'اسم 2',
                'code_etab' => '26258R',
                'code_niveau' => 'PRIM2'
            ],
            [
                'code_eleve' => 'ELEVE127680003',
                'nom_eleve_ar' => 'طالب 3',
                'prenom_eleve_ar' => 'اسم 3',
                'code_etab' => '26258R',
                'code_niveau' => 'PRIM2'
            ],
            [
                'code_eleve' => 'ELEVE127680004',
                'nom_eleve_ar' => 'طالب 4',
                'prenom_eleve_ar' => 'اسم 4',
                'code_etab' => '26258R',
                'code_niveau' => 'PRIM2'
            ],
            [
                'code_eleve' => 'ELEVE127680005',
                'nom_eleve_ar' => 'طالب 5',
                'prenom_eleve_ar' => 'اسم 5',
                'code_etab' => '26258R',
                'code_niveau' => 'PRIM2'
            ],
            [
                'code_eleve' => 'ELEVE127680006',
                'nom_eleve_ar' => 'طالب 6',
                'prenom_eleve_ar' => 'اسم 6',
                'code_etab' => '26258R',
                'code_niveau' => 'PRIM2'
            ],
            [
                'code_eleve' => 'ELEVE127680007',
                'nom_eleve_ar' => 'طالب 7',
                'prenom_eleve_ar' => 'اسم 7',
                'code_etab' => '26258R',
                'code_niveau' => 'PRIM2'
            ],
            [
                'code_eleve' => 'ELEVE127680008',
                'nom_eleve_ar' => 'طالب 8',
                'prenom_eleve_ar' => 'اسم 8',
                'code_etab' => '26258R',
                'code_niveau' => 'PRIM2'
            ],
            [
                'code_eleve' => 'ELEVE127680009',
                'nom_eleve_ar' => 'طالب 9',
                'prenom_eleve_ar' => 'اسم 9',
                'code_etab' => '26258R',
                'code_niveau' => 'PRIM2'
            ],
             [
                'code_eleve' => 'ELEVE127680010',
                'nom_eleve_ar' => 'طالب 10',
                'prenom_eleve_ar' => 'اسم 10',
                'code_etab' => '26258R',
                'code_niveau' => 'PRIM2'
            ],
            // Ajoutez d'autres élèves ici
        ];

        foreach ($eleves as $eleve) {
            try {
                Eleve::create($eleve);
                $this->command->info("Élève créé avec succès : {$eleve['code_eleve']}");
            } catch (\Exception $e) {
                $this->command->error("Erreur lors de la création de l'élève {$eleve['code_eleve']} : " . $e->getMessage());
            }
        }

        $this->command->info('Élèves créés avec succès !');
    }
}
