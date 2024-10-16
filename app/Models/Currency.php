<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\RawMaterial;

class Currency extends Model
{
    use HasFactory;
    protected $fillable = [
        'base_currency_name',
        'symbol',
        'base_currency_value',
        'target_currency_name',
        'target_currency_value',
        'exchage_rate'
    ];
    

    public function raw_materials (){
        return $this -> hasOne(RawMaterial::class);
    }

}
