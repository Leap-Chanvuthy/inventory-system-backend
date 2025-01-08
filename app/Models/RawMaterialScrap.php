<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\RawMaterial;

class RawMaterialScrap extends Model
{
    use HasFactory;
    use SoftDeletes;
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
