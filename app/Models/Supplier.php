<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\RawMaterial;
use App\Models\PurchaseInvoice;

class Supplier extends Model
{
    use HasFactory;

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

    public function purchase_invoices()
    {
        return $this->hasMany(PurchaseInvoice::class, 'supplier_id');
    }
}
