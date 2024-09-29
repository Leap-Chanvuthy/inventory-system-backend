<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Supplier;
use App\Models\PurchaseInvoiceDetail;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseInvoice extends Model
{
    use HasFactory;
    use SoftDeletes;
    
    protected $table = 'purchase_invoices';

    protected $fillable = [
        'payment_method',
        'invoice_number',
        'payment_date',
        'discount_percentage',
        'discount_value',
        'tax_percentage',
        'tax_value',
        'status',
        'sub_total',
        'grand_total_with_tax',
        'grand_total_without_tax',
        'clearing_payable',
        'indebted',
        'supplier_id',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseInvoiceDetails(){
        return $this -> hasMany(PurchaseInvoiceDetail::class , 'purchase_invoice_id');
    }

}
