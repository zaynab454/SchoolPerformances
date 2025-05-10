<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Province;
use App\Models\Etablissement;

class Commune extends Model
{
    use HasFactory;

    protected $table = 'commune';
    protected $primaryKey = 'cd_com';

    protected $fillable = [
        'll_com',
        'la_com',
        'id_province',
        'cycle'
    ];

    public function province()
    {
        return $this->belongsTo(Province::class, 'id_province');
    }

    public function etablissements()
    {
        return $this->hasMany(Etablissement::class, 'code_commune', 'cd_com');
    }
}
