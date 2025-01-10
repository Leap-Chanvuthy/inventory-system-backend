<?php

namespace App\Exports;

use App\Models\RawMaterialScrap;
use Maatwebsite\Excel\Concerns\FromCollection;
use Spatie\QueryBuilder\QueryBuilder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Builder;

class RawMaterialScrapExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $request; 

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        return QueryBuilder::for(RawMaterialScrap::class)
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('quantity'),
                AllowedFilter::exact('raw_material_id'),
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


    public function map($rawMaterialScrap): array
    {
        return [
            $rawMaterialScrap -> id,
            $rawMaterialScrap -> quantity,
            $rawMaterialScrap -> reason,
            $rawMaterialScrap -> raw_material->id,
            $rawMaterialScrap -> raw_material->name,
            $rawMaterialScrap -> raw_material->material_code,
            $rawMaterialScrap -> raw_material->quantity,
            $rawMaterialScrap -> raw_material->remaining_quantity,
            $rawMaterialScrap -> raw_material->unit_price_in_usd,
            $rawMaterialScrap -> raw_material->total_value_in_usd,
            $rawMaterialScrap -> raw_material->unit_price_in_riel,
            $rawMaterialScrap -> raw_material->total_value_in_riel,
            $rawMaterialScrap -> raw_material->minimum_stock_level,
            $rawMaterialScrap -> raw_material->unit_of_measurement,
            $rawMaterialScrap -> raw_material->package_size,
            $rawMaterialScrap -> raw_material->status,
            $rawMaterialScrap -> raw_material -> category -> category_name ?? 'N/A',
            $rawMaterialScrap -> raw_material->location,
            $rawMaterialScrap -> raw_material->expiry_date,
            $rawMaterialScrap -> raw_material->supplier->name ?? 'N/A',         
            $rawMaterialScrap -> raw_material->supplier->phone_number ?? 'N/A',
            $rawMaterialScrap -> raw_material->supplier->email ?? 'N/A',
            $rawMaterialScrap -> raw_material->supplier->location ?? 'N/A',
            $rawMaterialScrap -> raw_material->created_at,
            $rawMaterialScrap -> raw_material->updated_at,
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Scrap Quantity',
            'Scrap Reason',
            'Raw Material ID',
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
