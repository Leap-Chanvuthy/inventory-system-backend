<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone_number',
        'location',
        'note',
    ];

    // Define the relationship with the Inventory model
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
