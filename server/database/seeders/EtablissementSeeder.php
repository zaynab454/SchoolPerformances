<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Etablissement;

class EtablissementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = database_path('data/etablissements.csv');
        
        if (!file_exists($csvFile)) {
            throw new \Exception("Le fichier CSV des établissements n'existe pas à l'emplacement: {$csvFile}");
        }

        $lines = array_map('str_getcsv', file($csvFile));
        $header = array_shift($lines);

        foreach ($lines as $line) {
            // Nettoyer la ligne en supprimant les espaces vides
            $cleanLine = array_map('trim', $line);
            
            // Vérifier que la ligne a le bon nombre de colonnes
            if (count($cleanLine) >= 5) {
                // Créer un tableau avec les valeurs nécessaires
                $data = [
                    'code_etab' => $cleanLine[3],
                    'nom_etab_fr' => $cleanLine[2],
                    'nom_etab_ar' => $cleanLine[1],
                    'code_commune' => $cleanLine[0],
                    'cycle' => $cleanLine[4]
                ];

                // Nettoyer les valeurs en supprimant les espaces multiples
                $code_etab = preg_replace('/\s+/', ' ', $data['code_etab']);
                $nom_etab_fr = preg_replace('/\s+/', ' ', $data['nom_etab_fr']);
                $nom_etab_ar = preg_replace('/\s+/', ' ', $data['nom_etab_ar']);
                $code_commune = preg_replace('/\s+/', ' ', $data['code_commune']);
                $cycle = preg_replace('/\s+/', ' ', $data['cycle']);

                // Vérifier que les valeurs ne sont pas vides
                if (!empty($code_etab) && !empty($nom_etab_fr) && !empty($nom_etab_ar) && !empty($code_commune) && !empty($cycle)) {
                    // Vérifier que le code établissement est valide
                    if (preg_match('/^[A-Z0-9]+$/', $code_etab)) {
                        Etablissement::create([
                            'code_etab' => $code_etab,
                            'nom_etab_fr' => $nom_etab_fr,
                            'nom_etab_ar' => $nom_etab_ar,
                            'code_commune' => $code_commune,
                            'cycle' => $cycle
                        ]);
                    }
                }
            }
        }
    }
}
