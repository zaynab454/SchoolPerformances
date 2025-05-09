<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Eleve;
use App\Models\Matiere;

class ResultatEleve extends Model
{
    use HasFactory;

    protected $table = 'resultat_eleve';
    protected $primaryKey = 'id_resultat';

    protected $fillable = [
        'code_eleve',
        'id_matiere',
        'annee_scolaire',
        'session',
        'MoyenNoteCC',
        'MoyenExamenNote',
        'MoyenCC',
        'MoyenExam',
        'MoyenSession'
    ];

    public function eleve()
    {
        return $this->belongsTo(Eleve::class, 'code_eleve');
    }

    public function matiere()
    {
        return $this->belongsTo(Matiere::class, 'id_matiere');
    }
}
