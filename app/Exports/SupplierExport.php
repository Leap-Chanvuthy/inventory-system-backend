<?php

namespace App\Exports;

use App\Models\Supplier;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class SupplierExport implements FromQuery, WithHeadings
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * Query to export filtered suppliers with explicit column selection
     */
    public function query()
    {
        // Using QueryBuilder for filtering suppliers
        return QueryBuilder::for(Supplier::class)
            ->allowedFilters([
                AllowedFilter::partial('location'),
                AllowedFilter::partial('city'),
                AllowedFilter::partial('bank_name'),
            ])
            ->allowedSorts('name', 'city', 'created_at', 'updated_at')
            ->defaultSort('-created_at')
            ->select([
                'name',
                'phone_number',
                'location',
                'longitude',
                'latitude',
                'address',
                'city',
                'email',
                'contact_person',
                'business_registration_number',
                'vat_number',
                'bank_account_number',
                'bank_account_name',
                'bank_name',
                'note',
                'created_at',
                'updated_at',
            ]);
    }

    /**
     * Headings for the export
     */
    public function headings(): array
    {
        return [
            'name',
            'phone_number',
            'location',
            'longitude',
            'latitude',
            'address',
            'city',
            'email',
            'contact_person',
            'business_registration_number',
            'vat_number',
            'bank_account_number',
            'bank_account_name',
            'bank_name',
            'note',
            'created_at',
            'updated_at',
        ];
    }
}
