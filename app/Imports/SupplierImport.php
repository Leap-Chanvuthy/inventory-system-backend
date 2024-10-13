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
            'name' => "required|string|max:255",
            'supplier_code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('suppliers'),
            ],
            // 'phone_number' => "required|string|max:50",
            'location' => "required|string|max:255",
            // 'longitude' => "nullable|string|max:100",
            // 'latitude' => "nullable|string|max:100",
            // 'address' => "nullable|string|max:255",
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('suppliers'),
            ],
            'contact_person' => "required|string|max:255",
            // 'website' => "nullable|string|max:255",
            // 'social_media' => "nullable|string|max:255",
            // 'supplier_category' => "nullable|string|max:255",
            // 'supplier_status' => "nullable|string|max:100",
            // 'contract_length' => "nullable|string|max:100",
            // 'discount_term' => "nullable|string|max:100",
            // 'payment_term' => "nullable|string|max:100",
            // 'business_registration_number' => "nullable|string|max:100",
            // 'vat_number' => "nullable|string|max:100",
            // 'bank_account_number' => "nullable|string|max:50",
            // 'bank_account_name' => "nullable|string|max:50",
            // 'bank_name' => "nullable|string|max:255",
            // 'note' => "nullable|string",
        ];
    }
    
}
