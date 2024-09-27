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
                AllowedFilter::partial('bank_name'),
                AllowedFilter::exact('supplier_status'),
                AllowedFilter::exact('supplier_category'),
                AllowedFilter::partial('contract_length'),
                AllowedFilter::partial('discount_term'),
                AllowedFilter::partial('payment_term'),
            ])
            ->allowedSorts('name' ,'created_at', 'updated_at')
            ->defaultSort('-created_at')
            ->select([
                'name',
                'supplier_code',
                'phone_number',
                'location',
                'longitude',
                'latitude',
                'address',
                'email',
                'contact_person',
                'website', 
                'social_media', 
                'supplier_category', 
                'supplier_status', 
                'contract_length', 
                'discount_term', 
                'payment_term', 
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
            'supplier_code',
            'phone_number',
            'location',
            'longitude',
            'latitude',
            'address',
            'email',
            'contact_person',
            'website', 
            'social_media', 
            'supplier_category', 
            'supplier_status', 
            'contract_length', 
            'discount_term', 
            'payment_term', 
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
