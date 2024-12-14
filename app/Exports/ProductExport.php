<?php

namespace App\Exports;

use App\Models\Product;
use Spatie\QueryBuilder\QueryBuilder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;
use Spatie\QueryBuilder\AllowedFilter;

class ProductExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        return QueryBuilder::for(Product::with(['raw_materials', 'category']))
            ->allowedIncludes(['raw_materials', 'category'])
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('product_name'),
                AllowedFilter::exact('product_code'),
                AllowedFilter::exact('quantity'),
                AllowedFilter::exact('remaining_quantity'),
                AllowedFilter::exact('unit_price_in_usd'),
                AllowedFilter::exact('total_value_in_usd'),
                AllowedFilter::exact('minimum_stock_level'),
                AllowedFilter::exact('unit_of_measurement'),
                AllowedFilter::exact('package_size'),
                AllowedFilter::exact('warehouse_location'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('product_category_id'),
                AllowedFilter::callback('start_date', function ($query, $value) {
                    $query->where('created_at', '>=', $value);
                }),
                AllowedFilter::callback('end_date', function ($query, $value) {
                    $query->where('created_at', '<=', $value);
                }),
            ])
            ->allowedSorts('created_at', 'quantity', 'minimum_stock_level')
            ->defaultSort('-created_at');
    }

    public function map($product): array
    {
        $data = [];

        foreach ($product->raw_materials as $rawMaterial) {
            $data[] = [
                $product->id,
                $product->product_name,
                $product->product_code,
                $rawMaterial->id,
                $rawMaterial->name,
                $rawMaterial->material_code,
                $rawMaterial->pivot->quantity_used ?? 'N/A',
                $product->quantity,
                $product->remaining_quantity,
                $product->unit_price_in_usd,
                $product->total_value_in_usd,
                $product->minimum_stock_level,
                $product->unit_of_measurement,
                $product->package_size,
                $product->warehouse_location,
                $product->status,
                $product->category->category_name ?? 'N/A',
                $product->created_at,
                $product->updated_at,
            ];
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Product ID',
            'Product Name',
            'Product Code',
            'Raw Material ID',
            'Raw Material Name',
            'Raw Material Code',
            'Raw Material Quantity Used',
            'Product Quantity',
            'Product Remaining Quantity',
            'Unit Price (USD)',
            'Total Value (USD)',
            'Minimum Stock Level',
            'Unit of Measurement',
            'Package Size',
            'Warehouse Location',
            'Product Status',
            'Product Category',
            'Created At',
            'Updated At',
        ];
    }
}
