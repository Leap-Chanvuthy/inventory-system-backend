<?php

namespace App\Exports;

use App\Models\PurchaseInvoice;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;

class PurchaseInvoiceExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        return QueryBuilder::for(PurchaseInvoice::with([
            'purchaseInvoiceDetails.rawMaterial',
            'purchaseInvoiceDetails.rawMaterial.category',
            'supplier',
        ]))
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('payment_method'),
                AllowedFilter::exact('discount_percentage'),
                AllowedFilter::exact('tax_percentage'),
                AllowedFilter::exact('clearing_payable_percentage'),
                AllowedFilter::exact('invoice_number'),
                AllowedFilter::callback('start_date', function ($query, $value) {
                    $query->where('created_at', '>=', $value);
                }),
                AllowedFilter::callback('end_date', function ($query, $value) {
                    $query->where('created_at', '<=', $value);
                }),
            ])
            ->allowedSorts('created_at', 'tax_percentage' , 'discount_percentage' , 'clearing_payable_percentage')
            ->defaultSort('-created_at');
    }

    public function map($purchaseInvoice): array
    {
        $data = [];
    
        foreach ($purchaseInvoice->purchaseInvoiceDetails as $detail) {
            $data[] = [
                $purchaseInvoice->id,
                $purchaseInvoice->invoice_number,
                $purchaseInvoice->payment_method,
                $purchaseInvoice->payment_date,
                $purchaseInvoice->status,
                $purchaseInvoice->supplier->name ?? 'N/A',
                $purchaseInvoice->supplier->supplier_code ?? 'N/A',
                $purchaseInvoice->supplier-> email ?? 'N/A',
                $purchaseInvoice->discount_percentage ?? 0,
                $purchaseInvoice->tax_percentage ?? 0,
                $purchaseInvoice->clearing_payable_percentage ?? 0,
                $purchaseInvoice->discount_value_in_riel ?? 0,
                $purchaseInvoice->discount_value_in_usd ?? 0,
                $purchaseInvoice->tax_value_in_riel ?? 0,
                $purchaseInvoice->tax_value_in_usd ?? 0,
                $purchaseInvoice->indebted_in_riel ?? 0,
                $purchaseInvoice->indebted_in_usd ?? 0,
                $purchaseInvoice->sub_total_in_riel ?: 0,
                $purchaseInvoice->sub_total_in_usd ?: 0,
                $purchaseInvoice->grand_total_without_tax_in_riel ?: 0,
                $purchaseInvoice->grand_total_without_tax_in_usd ?: 0,
                $purchaseInvoice->grand_total_with_tax_in_riel ?: 0,
                $purchaseInvoice->grand_total_with_tax_in_usd ?: 0,
                $detail->id,
                $detail->quantity,
                $detail->total_price_in_riel ?: 0,
                $detail->total_price_in_usd ?: 0,
                $detail->rawMaterial->name ?? 'N/A',
                $detail->rawMaterial->material_code ?? 'N/A',
                $purchaseInvoice->created_at->format('Y-m-d'),
            ];
        }
    
        return $data;
    }
    

    public function headings(): array
    {
        return [
            'Invoice ID',
            'Invoice Number',
            'Payment Method',
            'Payment Date',
            'Status',
            'Supplier Name',
            'Supplier Code',
            'Supplier Email',
            'Discount (%)',
            'Tax (%)',
            'Payable Rate (%)',
            'Discount Amount (៛)',
            'Discount Amouunt ($)',
            'Tax Amount (៛)',
            'Tax Amount ($)',
            'Indebted (៛)',
            'Indebted ($)',
            'Sub Total (៛)',
            'Sub Total ($)',
            'Grand Total Without Tax (៛)',
            'Grand Total Without Tax ($)',
            'Grand Total With Tax (៛)',
            'Grand Total With Tax ($)',
            'Invoice Detail ID',
            'Quantity',
            'Total Price (៛)',
            'Total Price ($)',
            'Raw Material Name',
            'Material Code',
            'Created At',
        ];
    }
}
