<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RawMaterialImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'image',
        'raw_material_id'
    ];


    public function supplier(){
        return $this -> belongsTo(Supplier::class);
    }

    public function raw_material(){
        return $this -> belongsTo(RawMaterial::class);
    }
}
