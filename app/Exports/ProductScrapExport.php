<?php

namespace App\Exports;

use App\Models\ProductScrap;
use Maatwebsite\Excel\Concerns\FromCollection;
use Spatie\QueryBuilder\QueryBuilder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Builder;

class ProductScrapExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $request; 

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        return QueryBuilder::for(ProductScrap::class)
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('quantity'),
                AllowedFilter::exact('product_id'),
                AllowedFilter::callback('start_date', function ($query, $value) {
                    $query->where('created_at', '>=', $value);
                }),
                AllowedFilter::callback('end_date', function ($query, $value) {
                    $query->where('created_at', '<=', $value);
                }),
            ])
            ->allowedSorts('created_at', 'updated_at', 'quantity')
            ->defaultSort('-created_at');
    
    }


    public function map($productScrap): array
    {
        return [
            $productScrap -> id,
            $productScrap -> quantity,
            $productScrap -> reason,
            $productScrap -> product->id,
            $productScrap -> product->product_name,
            $productScrap -> product->product_code,
            $productScrap -> product->quantity,
            $productScrap -> product->remaining_quantity,
            $productScrap -> product->unit_price_in_usd,
            $productScrap -> product->total_value_in_usd,
            $productScrap -> product->unit_price_in_riel,
            $productScrap -> product->total_value_in_riel,
            $productScrap -> product->minimum_stock_level,
            $productScrap -> product->unit_of_measurement,
            $productScrap -> product->package_size,
            $productScrap -> product->status,
            $productScrap -> product -> category -> category_name ?? 'N/A',
            $productScrap -> product->ware_houselocation,
            $productScrap -> product->created_at,
            $productScrap -> product->updated_at,
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Scrap Quantity',
            'Scrap Reason',
            'Product ID',
            'Name',
            'Product Code',
            'Quantity',
            'Remaining Quantity',
            'Unit Price (USD)',
            'Total Value (USD)',
            'Unit Price (Riel)',
            'Total Value (Riel)',
            'Minimum Stock Level',
            'Unit of Measurement',
            'Package Size',
            'Status',
            'Category',
            'Warehouse Location',
            'Created At',
            'Updated At',
        ];
    }
}
