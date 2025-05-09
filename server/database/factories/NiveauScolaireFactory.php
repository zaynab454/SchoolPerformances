<?php

namespace Database\Factories;

use App\Models\NiveauScolaire;
use Illuminate\Database\Eloquent\Factories\Factory;

class NiveauScolaireFactory extends Factory
{
    protected $model = NiveauScolaire::class;

    public function definition(): array
    {
        return [
            'code_niveau' => $this->faker->unique()->regexify('[A-Z]{4}[0-9]'),
            'description' => $this->faker->sentence(3)
        ];
    }
} 