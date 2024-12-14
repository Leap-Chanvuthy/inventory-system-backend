<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Customer;

class CustomerCategory extends Model
{
    use HasFactory;
    protected $fillable = [
        'category_name',
        'description',
    ];

    public function customers()
    {
        return $this->hasMany(Customer::class, 'customer_category_id');
    }
}
