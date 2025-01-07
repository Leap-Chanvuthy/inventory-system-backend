<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use App\Models\SaleOrder;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductSaleOrder extends Model
{
    use HasFactory;
    use SoftDeletes;
    // protected $table = 'product_sale_orders';
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
