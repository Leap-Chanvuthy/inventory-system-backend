<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleOrder extends Model
{
    use HasFactory;
    use SoftDeletes;
    // protected $fillable = [
    //     'payment_method',
    //     'order_date',
    //     'payment_status',
    //     'order_status',
    //     'discount_percentage',
    //     'discount_value_in_riel',
    //     'discount_value_in_usd',
    //     'tax_percentage',
    //     'tax_value_in_riel',
    //     'tax_value_in_usd',
    //     'sub_total_in_riel',
    //     'sub_total_in_usd',
    //     'grand_total_with_tax_in_riel',
    //     'grand_total_with_tax_in_usd',
    //     'grand_total_without_tax_in_riel',
    //     'grand_total_without_tax_in_usd',
    //     'clearing_payable_percentage',
    //     'indebted_in_riel',
    //     'indebted_in_usd',
    // ];


    protected $fillable = [
        'payment_method',
        'order_date',
        'payment_status',
        'sale_invoice_number',
        'order_status',
        'discount_percentage',
        'discount_value_in_usd',
        'discount_value_in_riel',
        'tax_percentage',
        'tax_value_in_usd',
        'tax_value_in_riel',
        'sub_total_in_usd',
        'sub_total_in_riel',
        'grand_total_with_tax_in_usd',
        'grand_total_with_tax_in_riel',
        'grand_total_without_tax_in_usd',
        'grand_total_without_tax_in_riel',
        'clearing_payable_percentage',
        'indebted_in_usd',
        'indebted_in_riel',
        'customer_id',
        'vender_id',
    ];
    


    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_sale_orders')
                    ->withPivot('quantity_sold');
    }


    public function customer (){
        return $this -> belongsTo(Customer::class);
    }

    public function vender (){
        return $this -> belongsTo(User::class);
    }

}
