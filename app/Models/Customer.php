<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\CustomerCategory;
use App\Models\SaleOrder;

class Customer extends Model
{
    use HasFactory , SoftDeletes;

    protected $fillable = [
        'image',
        'fullname',
        'email_address',
        'phone_number',
        'social_media',
        'shipping_address',
        'longitude',
        'latitude',
        'customer_status',
        'customer_category_id',
        'customer_note',
    ];


    public function category()
    {
        return $this->belongsTo(CustomerCategory::class, 'customer_category_id');
    }    

    public function sale_orders (){
        return $this -> hasMany(SaleOrder::class);
    }

}
