<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Supplier;
use App\Models\ProductRawMaterial;
use App\Models\Product;
use App\Models\RawMaterialCategory;
use App\Models\RawMaterialImage;

class RawMaterial extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        'name',
        'material_code',
        'quantity',
        'remaining_quantity',
        'unit_price_in_usd',
        'total_value_in_usd',
        'exchange_rate_from_usd_to_riel',
        'unit_price_in_riel',
        'total_value_in_riel',
        'exchange_rate_from_riel_to_usd',
        'minimum_stock_level',
        'raw_material_category',
        'unit_of_measurement',
        'package_size',
        'status',
        'location',
        'description',
        'expiry_date',
        'supplier_id',
        'raw_material_category_id'
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_raw_material')
                    ->withPivot('quantity_used');
                    // ->using(ProductRawMaterial::class)
                    // ->withTimestamps();
    }

    public function supplier(){
        return $this -> belongsTo(Supplier::class);
    }

    public function raw_material_images (){
        return $this -> hasMany(RawMaterialImage::class);
    }

    public function purchase_invoice_details()
    {
        return $this->hasMany(PurchaseInvoiceDetail::class, 'raw_material_id');
    }

    public function category()
    {
        return $this->belongsTo(RawMaterialCategory::class, 'raw_material_category_id');
    }    
}
