<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Admin;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Admin::create([
            'nom' => 'Administrateur',
            'email' => 'admin@schoolperformance.ma',
            'password' => bcrypt('admin123')
        ]);
    }
}
