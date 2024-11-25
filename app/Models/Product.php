<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\RawMaterial;
use App\Models\ProductRawMaterial;
use App\Models\ProductImage;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'staging_date',
        'product_name',
        'product_code',
        'quantity',
        'remaining_quantity',
        'minimum_stock_level',
        'unit_of_measurement',
        'package_size',
        'warehouse_location',
        'unit_price_in_usd',
        'total_value_in_usd',
        'exchange_rate_from_usd_to_riel',
        'unit_price_in_riel',
        'total_value_in_riel',
        'exchange_rate_from_riel_to_usd',
        'description',
        'status',
        'product_category_id',
    ];

    public function raw_materials()
    {
        return $this->belongsToMany(RawMaterial::class, 'product_raw_material')
                    ->withPivot('quantity_used')
                    ->using(ProductRawMaterial::class) 
                    ->withTimestamps();
    }

    public function product_images (){
        return $this -> hasMany(ProductImage::class);
    }

}
