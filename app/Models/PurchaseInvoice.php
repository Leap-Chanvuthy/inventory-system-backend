<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\PurchaseInvoiceDetail;

class PurchaseInvoice extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'purchase_invoices';

    protected $fillable = [
        'payment_method',
        'invoice_number',
        'payment_date',
        'discount_percentage',
        'discount_value_in_riel',
        'discount_value_in_usd',
        'tax_percentage',
        'tax_value_in_riel',
        'tax_value_in_usd',
        'status',
        'sub_total_in_riel',
        'sub_total_in_usd',
        'grand_total_with_tax_in_riel',
        'grand_total_with_tax_in_usd',
        'grand_total_without_tax_in_riel',
        'grand_total_without_tax_in_usd',
        'clearing_payable_in_riel',
        'clearing_payable_in_usd',
        'indebted_in_riel',
        'indebted_in_usd',
    ];

    public function purchaseInvoiceDetails(){
        return $this->hasMany(PurchaseInvoiceDetail::class, 'purchase_invoice_id');
    }
}
