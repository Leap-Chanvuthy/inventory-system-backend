<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use App\Models\SaleOrder;

class ProductSaleOrder extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id',
        'sale_order_id',
        'quantity_sold',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function sale_order()
    {
        return $this->belongsTo(SaleOrder::class);
    }
}
