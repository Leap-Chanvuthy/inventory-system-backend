<?php

namespace App\Imports;

use App\Models\RawMaterial;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class RawMaterialImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new RawMaterial([
            'name' => $row['name'],
            'quantity' => $row['quantity'],
            'unit_price' => $row['unit_price'],
            'total_value' => $row['total_value'],
            'minimum_stock_level' => $row['minimum_stock_level'],
            'unit' => $row['unit'],
            'package_size' => $row['package_size'],
            'supplier_id' => $row['supplier_id'],
            'product_id' => $row['product_id'] ?? null,
        ]);
    }
}
