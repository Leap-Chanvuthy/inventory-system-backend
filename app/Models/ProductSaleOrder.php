<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductSaleOrder extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id',
        'sale_order_id',
        'quantity_sold',
    ];
}
