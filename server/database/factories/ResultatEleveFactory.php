<?php

namespace Database\Factories;

use App\Models\ResultatEleve;
use App\Models\Eleve;
use App\Models\Matiere;
use App\Models\AnneeScolaire;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResultatEleveFactory extends Factory
{
    protected $model = ResultatEleve::class;

    public function definition(): array
    {
        $noteCC = $this->faker->numberBetween(0, 20);
        $noteExamen = $this->faker->numberBetween(0, 20);
        $moyenCC = $noteCC;
        $moyenExam = $noteExamen;
        $moyenSession = ($moyenCC + $moyenExam) / 2;

        return [
            'code_eleve' => Eleve::factory(),
            'id_matiere' => Matiere::factory(),
            'annee_scolaire' => AnneeScolaire::factory(),
            'session' => $this->faker->randomElement(['1', '2']),
            'MoyenNoteCC' => $noteCC,
            'MoyenExamenNote' => $noteExamen,
            'MoyenCC' => $moyenCC,
            'MoyenExam' => $moyenExam,
            'MoyenSession' => $moyenSession
        ];
    }
} 