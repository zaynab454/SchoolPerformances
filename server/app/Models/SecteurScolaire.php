<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Etablissement;

class SecteurScolaire extends Model
{
    use HasFactory;

    protected $table = 'secteur_scolaire';
    protected $primaryKey = 'id_secteur';

    protected $fillable = [
        'nom_secteur',
        'code_etab'
    ];

    public function etablissement()
    {
        return $this->belongsTo(Etablissement::class, 'code_etab');
    }
}
