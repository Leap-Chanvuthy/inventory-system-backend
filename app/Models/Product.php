<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use app\Models\Supplier;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'date',
        'item_name',
        'description',
        'quantity',
        'cost_per_item',
        'total_value',
        'category',
        'unit',
    ];

    // Define the relationship with the Supplier model
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
