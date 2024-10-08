<?php

// namespace App\Imports;

// use App\Models\Supplier;
// use Maatwebsite\Excel\Concerns\ToModel;
// use Maatwebsite\Excel\Concerns\WithHeadingRow;

// class SupplierImport implements ToModel, WithHeadingRow
// {
//     /**
//      * @param array $row
//      *
//      * @return Supplier|null
//      */
//     public function model(array $row)
//     {
//         return new Supplier([
//             'name' => $row['name'],
//             'phone_number' => $row['phone_number'],
//             'location' => $row['location'],
//             'note' => $row['note'],
//         ]);
//     }
// }



// namespace App\Imports;

// use App\Models\Supplier;
// use Maatwebsite\Excel\Concerns\ToModel;
// use Maatwebsite\Excel\Concerns\WithHeadingRow;
// use Maatwebsite\Excel\Concerns\WithValidation;
// use Maatwebsite\Excel\Concerns\SkipsOnFailure;
// use Maatwebsite\Excel\Concerns\SkipsFailures;
// use Maatwebsite\Excel\Concerns\Importable;
// use Illuminate\Validation\Rule;

// class SupplierImport implements ToModel, WithHeadingRow
// {
//     use Importable, SkipsFailures;

//     /**
//      * @param array $row
//      *
//      * @return Supplier|null
//      */
//     public function model(array $row)
//     {
//         return new Supplier([
//             'name' => $row['name'],
//             'phone_number' => $row['phone_number'],
//             'location' => $row['location'],
//             'longitude' => $row['longitude'],
//             'latitude' => $row['latitude'],
//             'address' => $row['address'],
//             'city' => $row['city'],
//             'email' => $row['email'],
//             'contact_person' => $row['contact_person'],
//             'business_registration_number' => $row['business_registration_number'],
//             'vat_number' => $row['vat_number'],
//             'bank_account_number' => $row['bank_account_number'],
//             'bank_account_name' => $row['bank_account_name'],
//             'bank_name' => $row['bank_name'],
//             'note' => $row['note'],
//         ]);
//     }


// }



namespace App\Imports;

use App\Models\Supplier;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Validation\Rule;

class SupplierImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable, SkipsFailures;

    /**
     * @param array $row
     *
     * @return Supplier|null
     */
    public function model(array $row)
    {
        return new Supplier([
            'name'                        => $row['name'],
            'phone_number'                => $row['phone_number'],
            'location'                    => $row['location'],
            'longitude'                   => $row['longitude'],
            'latitude'                    => $row['latitude'],
            'address'                     => $row['address'],
            'email'                       => $row['email'],
            'contact_person'              => $row['contact_person'],
            'business_registration_number'=> $row['business_registration_number'],
            'vat_number'                  => $row['vat_number'],
            'bank_account_number'         => $row['bank_account_number'],
            'bank_account_name'           => $row['bank_account_name'],
            'bank_name'                   => $row['bank_name'],
            'supplier_code'               => $row['supplier_code'],
            'website'                     => $row['website'],
            'social_media'                => $row['social_media'],
            'supplier_category'           => $row['supplier_category'],
            'supplier_status'             => $row['supplier_status'],
            'contract_length'             => $row['contract_length'],
            'discount_term'               => $row['discount_term'],
            'payment_term'                => $row['payment_term'],
            'note'                => $row['note'],
        ]); 
    }  

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => "required|string|max:50",
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('suppliers'),
            ],
        ];
    }
}
