<?php

namespace App\Imports;

use App\Models\Customer;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Validation\Rule;

class CustomerImport implements ToModel, WithValidation, WithHeadingRow
{
    use Importable, SkipsFailures;

    /**
     * @param array $row
     *
     * @return Customer|null
     */

    public function model(array $row)
    {
        return new Customer([
            'fullname' => $row['fullname'],
            'email_address' => $row['email_address'],
            'phone_number' => $row['phone_number'],
            'social_media' => $row['social_media'],
            'shipping_address' => $row['shipping_address'],
            'longitude' => $row['longitude'],
            'latitude' => $row['latitude'],
            'customer_status' => $row['customer_status'],
            'customer_category_id' => $row['customer_category_id'],
            'customer_note' => $row['customer_note'],
        ]);
    }

    /**
     * Define validation rules for the import.
     */
    public function rules(): array
    {
        return [
            'fullname' => 'required|string|max:50',
            'email_address' => 'nullable|email|max:50',
            'phone_number' => 'required|string|max:50',
            'social_media' => 'nullable|string|max:100',
            'shipping_address' => 'required|string|max:255',
            'longitude' => 'nullable',
            'latitude' => 'nullable',
            'customer_status' => [
                'required',
                Rule::in(['ACTIVE', 'INACTIVE', 'SUSPENDED']),
            ],
            'customer_category_id' => 'required|exists:customer_categories,id',
            'customer_note' => 'nullable|string',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'customer_status.in' => 'Supplier status must be ACTIVE, INACTIVE, or SUSPENDED.',
        ];
    }


}
