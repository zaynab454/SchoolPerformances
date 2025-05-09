<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Commune;

class CommuneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = database_path('data/communes.csv');
        
        if (!file_exists($csvFile)) {
            throw new \Exception("Le fichier CSV des communes n'existe pas à l'emplacement: {$csvFile}");
        }

        $lines = array_map('str_getcsv', file($csvFile));
        $header = array_shift($lines);

        foreach ($lines as $line) {
            $data = array_combine($header, $line);
            Commune::create([
                'cd_com' => $data['cd_com'],
                'la_com' => $data['la_com'],
                'll_com' => $data['ll_com'],
                'id_province' => '18284',
                'cycle' => $data['cycle']
            ]);
        }
    }
}
