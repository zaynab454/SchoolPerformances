<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ResultatEleve;

class Matiere extends Model
{
    use HasFactory;

    protected $table = 'matiere';
    protected $primaryKey = 'id_matiere';

    protected $fillable = [
        'nom_matiere_ar'
    ];

    public function resultats()
    {
        return $this->hasMany(ResultatEleve::class, 'id_matiere');
    }
}
