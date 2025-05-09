<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rapport extends Model
{
    use HasFactory;

    protected $table = 'rapport';

    protected $fillable = [
        'type_rapport',
        'nom_rapport',
        'contenu'
    ];
}
