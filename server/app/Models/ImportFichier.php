<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ImportFichier extends Model
{
    use HasFactory;

    protected $table = 'import_fichiers';

    protected $fillable = [
        'type_fichier',
        'nom_fichier',
        'statut'
    ];
}
