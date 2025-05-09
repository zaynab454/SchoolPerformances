<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ResultatEleve;

class AnneeScolaire extends Model
{
    use HasFactory;

    protected $table = 'annee_scolaire';
    protected $primaryKey = 'id_annee';

    protected $fillable = [
        'annee_scolaire',
        'est_courante'
    ];

    public function resultats()
    {
        return $this->hasMany(ResultatEleve::class, 'annee_scolaire');
    }
}
