<?php

namespace App\Exports;

use App\Models\Supplier;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SupplierExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Supplier::all();
    }

    /**
     * @return array
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
        ];
    }
}
