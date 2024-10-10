<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\RawMaterial;
use App\Models\ProductRawMaterial;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'date',
        'item_name',
        'description',
        'quantity',
        'cost_per_item',
        'total_value',
        'category',
        'unit',
    ];

    public function raw_materials()
    {
        return $this->belongsToMany(RawMaterial::class, 'product_raw_material')
                    ->withPivot('quantity_used')
                    ->using(ProductRawMaterial::class) 
                    ->withTimestamps();
    }

}
