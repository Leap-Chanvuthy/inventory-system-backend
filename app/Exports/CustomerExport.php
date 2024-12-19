<?php

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;

class CustomerExport implements FromQuery , WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    use Exportable;
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }



    public function query()
    {
        return QueryBuilder::for(Customer::with('category'))
            ->allowedIncludes(['category'])
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('customer_category_id'),
                AllowedFilter::exact('customer_status'),
                AllowedFilter::partial('phone_number'),
                AllowedFilter::partial('email_address'),
                AllowedFilter::callback('search', function (Builder $query, $value) {
                    $query->where(function ($query) use ($value) {
                        $query->where('customer_category_id', 'LIKE', "%{$value}%");
                        $query->where('customer_status', 'LIKE', "%{$value}%"); 
                        $query->where('fullname', 'LIKE', "%{$value}%");
                        $query->where('email_address', 'LIKE', "%{$value}%");  
                        $query->where('shipping_address', 'LIKE', "%{$value}%");
                        $query->where('phone_number', 'LIKE', "%{$value}%");
                        $query->where('social_media', 'LIKE', "%{$value}%");                         
                    });
                })
            ])
            ->allowedSorts('created_at', 'updated_at')
            ->defaultSort('-created_at');
    }


    public function map($customer): array
    {
        return [
            $customer->id,
            $customer->fullname,
            $customer -> email_address,
            $customer -> phone_number,
            $customer -> social_media,
            $customer -> shipping_address,
            $customer -> status,
            $customer -> category_id,
            $customer -> category -> category_name,
            $customer -> customer_note,
            $customer->created_at,
            $customer->updated_at,
        ];
    }



    public function headings(): array {
        return [
            'ID',
            'Fullname',
            'Email Address',
            'Phone Number',
            'Social Media',
            'Shipping Address',
            'Customer Status',
            'Category ID',
            'Category Name',
            'Customer Note',
            'Created',
            'Updated'
        ];
    }

}
