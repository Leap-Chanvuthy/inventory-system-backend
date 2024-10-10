<?php

// namespace App\Imports;

// use App\Models\RawMaterial;
// use Maatwebsite\Excel\Concerns\ToModel;
// use Maatwebsite\Excel\Concerns\WithHeadingRow;

// class RawMaterialImport implements ToModel, WithHeadingRow
// {
//     public function model(array $row)
//     {
//         return new RawMaterial([
//             'name' => $row['name'],
//             'quantity' => $row['quantity'],
//             'unit_price' => $row['unit_price'],
//             'total_value' => $row['total_value'],
//             'minimum_stock_level' => $row['minimum_stock_level'],
//             'unit' => $row['unit'],
//             'package_size' => $row['package_size'],
//             'supplier_id' => $row['supplier_id'],
//         ]);
//     }
// }



namespace App\Imports;

use App\Models\RawMaterial;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\WithValidation;

class RawMaterialImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        // Create and return a new RawMaterial instance
        return new RawMaterial([
            'name' => $row['name'],
            'material_code' => $row['material_code'], // Added missing field
            'quantity' => $row['quantity'],
            'unit_price' => $row['unit_price'],
            'total_value' => $row['total_value'],
            'minimum_stock_level' => $row['minimum_stock_level'],
            'raw_material_category' => $row['raw_material_category'], // Added missing field
            'unit_of_measurement' => $row['unit_of_measurement'], // Added missing field
            'package_size' => $row['package_size'],
            'status' => $row['status'], // Added missing field
            'location' => $row['location'], // Added missing field
            'description' => $row['description'], // Added missing field
            'expiry_date' => $row['expiry_date'], // Added missing field
            'supplier_id' => $row['supplier_id'],
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:50',
            'material_code' => 'required|string|max:255',
            'quantity' => 'required|integer',
            'unit_price' => 'required|numeric',
            'total_value' => 'required|numeric',
            'minimum_stock_level' => 'required|integer',
            'raw_material_category' => 'required|string|max:100',
            'unit_of_measurement' => 'required|string|max:100',
            'package_size' => 'nullable|string|max:100',
            'status' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'expiry_date' => 'nullable|date',
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
        ];
    }

    // public function customValidationMessages()
    // {
    //     return [
    //         'name.required' => 'The name field is required.',
    //         'material_code.required' => 'The material code field is required.',
    //         'quantity.required' => 'The quantity field is required.',
    //         'unit_price.required' => 'The unit price field is required.',
    //         'total_value.required' => 'The total value field is required.',
    //         'minimum_stock_level.required' => 'The minimum stock level field is required.',
    //         'raw_material_category.required' => 'The raw material category field is required.',
    //         'unit_of_measurement.required' => 'The unit of measurement field is required.',
    //         // Add more custom messages as needed
    //     ];
    // }
}
