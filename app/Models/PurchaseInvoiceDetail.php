<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\PurchaseInvoice;
use App\Models\RawMaterial;
use App\Models\Supplier;

class PurchaseInvoiceDetail extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'purchase_invoice_details';

    protected $fillable = [
        'quantity',
        'total_price_in_riel',
        'total_price_in_usd',
        'purchase_invoice_id',
        'raw_material_id',
        'supplier_id',
    ];

    public function purchaseInvoice()
    {
        return $this->belongsTo(PurchaseInvoice::class);
    }

    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class);
    }

    // public function supplier()
    // {
    //     return $this->belongsTo(Supplier::class);
    // }
}
