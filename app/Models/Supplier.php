<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\RawMaterial;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'supplier_code',
        'name',
        'phone_number',
        'email',
        'contact_person',
        'business_registration_number',
        'vat_number',
        'website',
        'social_media',
        'supplier_category',
        'supplier_status',
        'contract_length',
        'discount_term',
        'payment_term',
        'location',
        'longitude',
        'latitude',
        'address',
        'city',
        'bank_account_number',
        'bank_account_name',
        'bank_name',
        'image',
        'note',
    ];


    public function raw_materials (){
        return $this -> hasMany(RawMaterial::class);
    }
}
