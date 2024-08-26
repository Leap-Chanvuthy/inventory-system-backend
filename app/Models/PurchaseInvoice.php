<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Supplier;
use App\Models\PurchaseInvoiceDetail;

class PurchaseInvoice extends Model
{
    use HasFactory;
    
    protected $table = 'purchase_invoices';

    protected $fillable = [
        'total_amount',
        'payment_method',
        'invoice_number',
        'payment_date',
        'discount_percentage',
        'discount_value',
        'tax_percentage',
        'tax_value',
        'status',
        'sub_total',
        'grand_total',
        'clearing_payable',
        'indebted',
        'supplier_id',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseInvoiceDetails(){
        return $this -> hasMany(PurchaseInvoiceDetail::class);
    }

}
