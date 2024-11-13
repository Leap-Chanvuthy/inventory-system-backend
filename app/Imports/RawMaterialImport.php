<?php

namespace App\Imports;

use App\Models\RawMaterial;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Validation\Rule;

class RawMaterialImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable, SkipsFailures;

    protected $existingMaterialCodes = [];

    /**
     * @param array $row
     *
     * @return RawMaterial|null
     */
    public function model(array $row)
    {
        $materialCode = $this->generateUniqueMaterialCode();

        return new RawMaterial([
            'name'                  => $row['name'],
            'material_code'         => $materialCode, // auto-generated material code
            'quantity'              => $row['quantity'],
            'remaining_quantity'    => $row['remaining_quantity'],
            'unit_price_in_usd'     => $row['unit_price_in_usd'],
            'total_value_in_usd'    => $row['total_value_in_usd'],
            'exchange_rate_from_usd_to_riel'    => $row['exchange_rate_from_usd_to_riel'],
            'unit_price_in_riel'    => $row['unit_price_in_riel'],
            'total_value_in_riel'   => $row['total_value_in_riel'],
            'exchange_rate_from_riel_to_usd'    => $row['exchange_rate_from_riel_to_usd'],
            'minimum_stock_level'   => $row['minimum_stock_level'],
            'unit_of_measurement'   => $row['unit_of_measurement'],
            'package_size'          => $row['package_size'],
            'status'                => $row['status'],
            'location'              => $row['location'],
            'description'           => $row['description'],
            'expiry_date'           => $row['expiry_date'],
            'supplier_id'           => $row['supplier_id'],
            'raw_material_category_id' => $row['raw_material_category_id']
        ]);
    }

    /**
     * Generate a unique material code.
     */
    private function generateUniqueMaterialCode(): string
    {
        // Fetch the highest material code from all raw materials, including soft-deleted ones
        $lastMaterial = RawMaterial::withTrashed()
            ->selectRaw('MAX(CAST(SUBSTRING(material_code, 5) AS UNSIGNED)) AS max_code')
            ->first();
    
        $lastCode = $lastMaterial->max_code ?? 0;
    
        $newNumber = str_pad($lastCode + 1, 6, '0', STR_PAD_LEFT);
        $newCode = 'MAT-' . $newNumber;
    
        while (in_array($newCode, $this->existingMaterialCodes) || RawMaterial::where('material_code', $newCode)->exists()) {
            $newNumber = str_pad($lastCode + 2, 6, '0', STR_PAD_LEFT);
            $newCode = 'MAT-' . $newNumber;
            $lastCode++;
        }
    
        $this->existingMaterialCodes[] = $newCode;
    
        return $newCode;
    }
    

    /**
     * Define the validation rules.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:50',
            'material_code' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('raw_materials'),
            ],
            'quantity' => 'required|integer',
            'remaining_quantity' => 'required|integer',
            'unit_price_in_usd' => 'required|numeric',
            'total_value_in_usd' => 'required|numeric',
            'exchange_rate_from_usd_to_riel' => 'required|numeric',
            'unit_price_in_riel' => 'required|numeric',
            'total_value_in_riel' => 'required|numeric',
            'exchange_rate_from_riel_to_usd' => 'required|numeric',
            'minimum_stock_level' => 'required|integer',
            'unit_of_measurement' => 'required|string|max:100',
            'package_size' => 'nullable|string|max:100',
            'status' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'expiry_date' => 'nullable|date',
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
            'raw_material_category_id' => 'nullable|integer|exists:raw_material_categories,id'
        ];
    }
}
