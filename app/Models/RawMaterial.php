<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Supplier;
use App\Models\Product;

class RawMaterial extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'quantity',
        'image',
        'unit_price',
        'total_value',
        'minimum_stock_level',
        'unit',
        'package_size',
        'supplier_id',
        'product_id',
    ];


    public function supplier(){
        return $this -> belongsTo(Supplier::class);
    }

    public function product(){
        return $this ->  belongsTo(Product::class);
    }
}
