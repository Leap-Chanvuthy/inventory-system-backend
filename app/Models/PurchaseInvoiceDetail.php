<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PurchaseInvoice;
use App\Models\RawMaterial;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseInvoiceDetail extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'purchase_invoice_details';

    
    protected $fillable = [
        'quantity',
        'total_price',
        'purchase_invoice_id',
        'raw_material_id',
    ];

    
    public function purchaseInvoice()
    {
        return $this->belongsTo(PurchaseInvoice::class);
    }

    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class);
    }
}
