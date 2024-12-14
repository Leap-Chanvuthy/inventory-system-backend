<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\CustomerCategory;

class Customer extends Model
{
    use HasFactory , SoftDeletes;

    protected $fillable = [
        'fullname',
        'email_address',
        'phone_number',
        'social_medial',
        'shipping_address',
        'customer_status',
        'customer_category_id',
        'customer_note',
    ];


    public function category()
    {
        return $this->belongsTo(CustomerCategory::class, 'customer_category_id');
    }    

}
