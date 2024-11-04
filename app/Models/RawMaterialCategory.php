<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\RawMaterial;

class RawMaterialCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_name',
        'description',
    ];

    public function raw_materials()
    {
        return $this->hasOne(RawMaterial::class, 'raw_material_category_id');
    }
    
}
