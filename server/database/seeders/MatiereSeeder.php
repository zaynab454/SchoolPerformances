<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Matiere;

class MatiereSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $matieres = [
            ['nom_matiere_ar' => 'اللغة العربية', 'code_niveau' => 'PRIM1', 'nom_colonne' => 'MoyenNoteCC'],
            ['nom_matiere_ar' => 'اللغة الفرنسية', 'code_niveau' => 'PRIM1', 'nom_colonne' => 'MoyenNoteCC'],
            ['nom_matiere_ar' => 'الرياضيات', 'code_niveau' => 'PRIM1', 'nom_colonne' => 'MoyenNoteCC'],
            ['nom_matiere_ar' => 'العلوم الطبيعية', 'code_niveau' => 'PRIM1', 'nom_colonne' => 'MoyenNoteCC'],
            ['nom_matiere_ar' => 'العلوم الاجتماعية', 'code_niveau' => 'PRIM1', 'nom_colonne' => 'MoyenNoteCC'],
            ['nom_matiere_ar' => 'التربية الإسلامية', 'code_niveau' => 'PRIM1', 'nom_colonne' => 'MoyenNoteCC'],
            ['nom_matiere_ar' => 'التربية البدنية', 'code_niveau' => 'PRIM1', 'nom_colonne' => 'MoyenNoteCC'],
            ['nom_matiere_ar' => 'التربية الفنية', 'code_niveau' => 'PRIM1', 'nom_colonne' => 'MoyenNoteCC'],
            ['nom_matiere_ar' => 'التربية موسيقية', 'code_niveau' => 'PRIM1', 'nom_colonne' => 'MoyenNoteCC'],
            ['nom_matiere_ar' => 'التربية تكنولوجية', 'code_niveau' => 'PRIM1', 'nom_colonne' => 'MoyenNoteCC']
        ];

        foreach ($matieres as $matiere) {
            Matiere::create($matiere);
        }

        $this->command->info('Matières créées avec succès !');
    }
} 