<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductRawMaterial extends Model
{
    // use SoftDeletes;
    protected $table = 'product_raw_material'; 
    protected $fillable = ['product_id', 'raw_material_id', 'quantity_used'];


    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class);
    }
}
