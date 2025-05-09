<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Commune;

class Province extends Model
{
    use HasFactory;

    protected $table = 'province';
    protected $primaryKey = 'id_province';

    protected $fillable = [
        'nom_province'
    ];

    public function communes()
    {
        return $this->hasMany(Commune::class, 'id_province');
    }
}
