<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Supplier;
use App\Models\ProductRawMaterial;
use App\Models\Product;
use App\Models\Currency;
use App\Models\RawMaterialImage;

class RawMaterial extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        'name',
        'material_code',
        'quantity',
        'unit_price',
        'total_value',
        'minimum_stock_level',
        'raw_material_category',
        'unit_of_measurement',
        'package_size',
        'status',
        'location',
        'description',
        'expiry_date',
        'supplier_id',
        'currency_id',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_raw_material')
                    ->withPivot('quantity_used')
                    ->using(ProductRawMaterial::class)
                    ->withTimestamps();
    }

    public function supplier(){
        return $this -> belongsTo(Supplier::class);
    }

    public function raw_material_images (){
        return $this -> hasMany(RawMaterialImage::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

}
