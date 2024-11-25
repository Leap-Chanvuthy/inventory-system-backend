<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class ProductCategory extends Model
{
    use HasFactory;
    protected $table = 'product_categories';

    protected $fillable = [
        'category_name',
        'description'
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'product_category_id');
    }
}
