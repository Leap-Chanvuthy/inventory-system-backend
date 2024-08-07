<?php

namespace App\Imports;

use App\Models\Supplier;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SupplierImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return Supplier|null
     */
    public function model(array $row)
    {
        return new Supplier([
            'name' => $row['name'],
            'phone_number' => $row['phone_number'],
            'location' => $row['location'],
            'note' => $row['note'],
        ]);
    }
}
