<?php

namespace App\Exports;

use App\Models\RawMaterial;
use Spatie\QueryBuilder\QueryBuilder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;
use Spatie\QueryBuilder\AllowedFilter;

class RawMaterialExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $request; 

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        return QueryBuilder::for(RawMaterial::with('supplier'))
        ->allowedIncludes(['supplier' , 'category'])
        ->allowedFilters([
            AllowedFilter::exact('id'),
            AllowedFilter::exact('name'),
            AllowedFilter::exact('material_code'),
            AllowedFilter::exact('quantity'),
            AllowedFilter::exact('remaining_quantity'),
            AllowedFilter::exact('unit_price_in_riel'),
            AllowedFilter::exact('total_value_in_riel'),
            AllowedFilter::exact('unit_price_in_usd'),
            AllowedFilter::exact('total_value_in_usd'),
            AllowedFilter::exact('minimum_stock_level'),
            AllowedFilter::exact('unit_of_measurement'),
            AllowedFilter::exact('package_size'),
            AllowedFilter::exact('status'),
            AllowedFilter::exact('raw_material_category_id'),
            AllowedFilter::callback('start_date', function ($query, $value) {
                $query->where('created_at', '>=', $value);
            }),
            AllowedFilter::callback('end_date', function ($query, $value) {
                $query->where('created_at', '<=', $value);
            }),
        ])
        ->allowedSorts('created_at', 'quantity', 'package_size', 'minimum_stock_level')
        ->defaultSort('-created_at');
    
    }


    public function map($rawMaterial): array
    {
        return [
            $rawMaterial->id,
            $rawMaterial->name,
            $rawMaterial->material_code,
            $rawMaterial->quantity,
            $rawMaterial->remaining_quantity,
            $rawMaterial->unit_price_in_usd,
            $rawMaterial->total_value_in_usd,
            $rawMaterial->unit_price_in_riel,
            $rawMaterial->total_value_in_riel,
            $rawMaterial->minimum_stock_level,
            $rawMaterial->unit_of_measurement,
            $rawMaterial->package_size,
            $rawMaterial->status,
            $rawMaterial -> category -> category_name ?? 'N/A',
            $rawMaterial->location,
            $rawMaterial->expiry_date,
            $rawMaterial->supplier->name ?? 'N/A',         
            $rawMaterial->supplier->phone_number ?? 'N/A',
            $rawMaterial->supplier->email ?? 'N/A',
            $rawMaterial->supplier->location ?? 'N/A',
            $rawMaterial->created_at,
            $rawMaterial->updated_at,
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Material Code',
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
            'Location',
            'Expiry Date',
            'Supplier Name',
            'Supplier Phone Number',
            'Supplier Email',
            'Supplier Location',
            'Created At',
            'Updated At',
        ];
    }
}
