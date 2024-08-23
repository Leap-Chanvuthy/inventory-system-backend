<?php

namespace App\Exports;

use App\Models\RawMaterial;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class RawMaterialExport implements FromQuery, WithHeadings
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $queryBuilder = QueryBuilder::for(RawMaterial::class)
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('name'),
                AllowedFilter::exact('quantity'),
                AllowedFilter::exact('unit_price'),
                AllowedFilter::exact('total_value'),
                AllowedFilter::exact('minimum_stock_level'),
                AllowedFilter::exact('unit'),
                AllowedFilter::exact('package_size'),
            ])
            ->allowedSorts('created_at', 'quantity', 'package_size', 'total_value', 'minimum_stock_level')
            ->defaultSort('-created_at');

        return $queryBuilder->getQuery();
    }

    public function headings(): array
    {
        return [
            'id',
            'name',
            'quantity',
            'unit_price',
            'total_value',
            'minimum_stock_level',
            'unit',
            'package_size',
            'supplier_id',
            'product_id',
            'created_at',
            'updated_at',
        ];
    }
}
