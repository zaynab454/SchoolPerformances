<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Etablissement;
use App\Models\NiveauScolaire;
use App\Models\ResultatEleve;

class Eleve extends Model
{
    use HasFactory;

    protected $table = 'eleve';
    protected $primaryKey = 'code_eleve';

    protected $fillable = [
        'nom_eleve_ar',
        'prenom_eleve_ar',
        'code_etab',
        'code_niveau'
    ];

    public function etablissement()
    {
        return $this->belongsTo(Etablissement::class, 'code_etab');
    }

    public function niveauScolaire()
    {
        return $this->belongsTo(NiveauScolaire::class, 'code_niveau');
    }

    public function resultats()
    {
        return $this->hasMany(ResultatEleve::class, 'code_eleve');
    }
}
