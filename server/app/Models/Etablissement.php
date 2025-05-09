<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Commune;
use App\Models\SecteurScolaire;
use App\Models\Eleve;

class Etablissement extends Model
{
    use HasFactory;

    protected $table = 'etablissement';
    protected $primaryKey = 'code_etab';

    protected $fillable = [
        'nom_etab_fr',
        'nom_etab_ar',
        'code_commune',
        'cycle'
    ];

    public function commune()
    {
        return $this->belongsTo(Commune::class, 'code_commune');
    }

    public function secteurScolaire()
    {
        return $this->belongsTo(SecteurScolaire::class, 'code_etab');
    }

    public function eleves()
    {
        return $this->hasMany(Eleve::class, 'code_etab');
    }
}
