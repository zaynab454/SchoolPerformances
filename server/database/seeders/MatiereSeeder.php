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
            ['nom_matiere_ar' => 'اللغة العربية'],
            ['nom_matiere_ar' => 'اللغة الفرنسية'],
            ['nom_matiere_ar' => 'الرياضيات'],
            ['nom_matiere_ar' => 'العلوم الطبيعية'],
            ['nom_matiere_ar' => 'العلوم الاجتماعية'],
            ['nom_matiere_ar' => 'التربية الإسلامية'],
            ['nom_matiere_ar' => 'التربية البدنية'],
            ['nom_matiere_ar' => 'التربية الفنية'],
            ['nom_matiere_ar' => 'التربية الموسيقية'],
            ['nom_matiere_ar' => 'التربية التكنولوجية']
        ];

        foreach ($matieres as $matiere) {
            Matiere::create($matiere);
        }

        $this->command->info('Matières créées avec succès !');
    }
} 