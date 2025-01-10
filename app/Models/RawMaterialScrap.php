<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\RawMaterial;

class RawMaterialScrap extends Model
{
    use HasFactory;
    protected $fillable = [
        'raw_material_id',
        'quantity',
        'reason',
    ];

    public function raw_material()
    {
        return $this->belongsTo(RawMaterial::class);
    }
}
