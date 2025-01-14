<?php

namespace App\Exports;

use App\Models\SaleOrder;
use Spatie\QueryBuilder\QueryBuilder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class SaleOrderExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        return QueryBuilder::for(SaleOrder::with(['customer', 'products' , 'vender']))
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('payment_method'),
                AllowedFilter::exact('order_status'),
                AllowedFilter::exact('payment_status'),
                AllowedFilter::exact('order_date'),
                AllowedFilter::exact('discount_percentage'),
                AllowedFilter::exact('tax_percentage'),
                AllowedFilter::exact('clearing_payable_percentage'),
                AllowedFilter::callback('search', function (Builder $query, $value) {
                    $query->where(function ($query) use ($value) {
                        $query->where('payment_method', 'LIKE', "%{$value}%")
                            ->orWhere('order_status', 'LIKE', "%{$value}%")
                            ->orWhere('payment_status', 'LIKE', "%{$value}%")
                            ->orWhere('order_date', 'LIKE', "%{$value}%");
                    });
                }),
                AllowedFilter::callback('start_date', function ($query, $value) {
                    $query->where('created_at', '>=', $value);
                }),
                AllowedFilter::callback('end_date', function ($query, $value) {
                    $query->where('created_at', '<=', $value);
                }),
            ])
            ->allowedSorts('created_at', 'updated_at', 'order_date', 'sub_total_in_usd', 'grand_total_with_tax_in_usd')
            ->defaultSort('-created_at');
    }

    public function map($saleOrder): array
    {
        $data = [];

        foreach ($saleOrder->products as $product) {
            $data[] = [
                $saleOrder->id,
                $saleOrder -> sale_invoice_number,
                $saleOrder->payment_method,
                $saleOrder->order_date,
                $saleOrder->payment_status,
                $saleOrder->order_status,
                $saleOrder->vender->name,
                $saleOrder->vender->email,
                $saleOrder->customer->id,
                $saleOrder->customer->fullname,
                $saleOrder->customer->phone_number ?? 'N/A',
                $saleOrder->customer->email ?? 'N/A',
                $saleOrder->customer->shipping_address,
                $product->product_code,
                $product->product_name,
                $product->pivot->quantity_sold ?? 'N/A',
                $saleOrder->discount_percentage,
                $saleOrder->discount_value_in_usd,
                $saleOrder->discount_value_in_riel,
                $saleOrder->tax_percentage,
                $saleOrder->tax_value_in_usd,
                $saleOrder->tax_value_in_riel,
                $saleOrder->sub_total_in_usd,
                $saleOrder->sub_total_in_riel,
                $saleOrder->grand_total_without_tax_in_usd,
                $saleOrder->grand_total_without_tax_in_riel,
                $saleOrder->grand_total_with_tax_in_usd,
                $saleOrder->grand_total_with_tax_in_riel,
                $saleOrder->clearing_payable_percentage,
                $saleOrder->indebted_in_usd,
                $saleOrder->indebted_in_riel,
            ];
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Sale Invoice Number',
            'Payment Method',
            'Order Date',
            'Payment Status',
            'Order Status',
            'Veder Name',
            'Vender Email',
            'Customer ID',
            'Customer Name',
            'Customer Phone',
            'Customer Email',
            'Shipping Address',
            'Product Code',
            'Product Name',
            'Quantity Ordered',
            'Discount (%)',
            'Discount Value (USD)',
            'Discount Value (Riel)',
            'Tax (%)',
            'Tax Value (USD)',
            'Tax Value (Riel)',
            'Sub Total (USD)',
            'Sub Total (Riel)',
            'Grand Total without Tax (USD)',
            'Grand Total without Tax (Riel)',
            'Grand Total with Tax (USD)',
            'Grand Total with Tax (Riel)',
            'Payable Rate (%)',
            'Indebted (USD)',
            'Indebted (Riel)',
        ];
    }
}
