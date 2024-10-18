<?php

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

    protected $existingSupplierCodes = [];

    /**
     * @param array $row
     *
     * @return Supplier|null
     */
    public function model(array $row)
    {
        $supplierCode = $this->generateUniqueSupplierCode();

        return new Supplier([
            'name'                        => $row['name'],
            'phone_number'                => $row['phone_number'],
            'location'                    => $row['location'],
            'longitude'                   => $row['longitude'],
            'latitude'                    => $row['latitude'],
            'address'                     => $row['address'],
            'email'                       => $row['email'],
            'contact_person'              => $row['contact_person'],
            'business_registration_number' => $row['business_registration_number'],
            'vat_number'                  => $row['vat_number'],
            'bank_account_number'         => $row['bank_account_number'],
            'bank_account_name'           => $row['bank_account_name'],
            'bank_name'                   => $row['bank_name'],
            'supplier_code'               => $supplierCode,
            'website'                     => $row['website'],
            'social_media'                => $row['social_media'],
            'supplier_category'           => $row['supplier_category'],
            'supplier_status'             => $row['supplier_status'],
            'contract_length'             => $row['contract_length'],
            'discount_term'               => $row['discount_term'],
            'payment_term'                => $row['payment_term'],
            'note'                        => $row['note'],
        ]); 
    }  

    private function generateUniqueSupplierCode(): string
    {
        $lastSupplier = Supplier::orderBy('created_at', 'desc')->first();
        if ($lastSupplier && preg_match('/SUPP-(\d{6})/', $lastSupplier->supplier_code, $matches)) {
            $lastCode = intval($matches[1]);
        } else {
            $lastCode = 0;
        }

        $newCode = null;
        do {
            $newNumber = str_pad($lastCode + 1, 6, '0', STR_PAD_LEFT);
            $newCode = 'SUPP-' . $newNumber;
            $lastCode++;
        } while (in_array($newCode, $this->existingSupplierCodes) || Supplier::where('supplier_code', $newCode)->exists());

        $this->existingSupplierCodes[] = $newCode;

        return $newCode;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => "required|string|max:255",
            'supplier_code' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('suppliers'),
            ],
            'location' => "required|string|max:255",
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('suppliers'),
            ],
            'contact_person' => "required|string|max:255",
        ];
    }
}
