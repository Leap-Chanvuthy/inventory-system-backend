<?php

namespace App\Http\Controllers\API;

use App\Imports\SupplierImport;
use App\Exports\SupplierExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\UploadedFile;
use App\Http\Controllers\Controller;

class SupplierAPIController extends Controller
{
    public function import(Request $request)
    {
        try {
            /** @var UploadedFile $file */
            $file = $request->file('supplier_file');

            if (!$file || !$file->isValid()) {
                return response()->json(['error' => 'Invalid file'], 400);
            }

            Excel::import(new SupplierImport, $file);

            return response()->json(['success' => 'Suppliers imported successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error importing suppliers: ' . $e->getMessage()], 500);
        }
    }

    public function export()
    {
        try {
            return Excel::download(new SupplierExport, 'suppliers.xlsx');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error exporting suppliers: ' . $e->getMessage()], 500);
        }
    }
}
