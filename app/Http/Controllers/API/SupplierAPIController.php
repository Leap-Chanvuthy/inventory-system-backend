<?php

namespace App\Http\Controllers\API;

use App\Imports\SupplierImport;
use App\Exports\SupplierExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\UploadedFile;
use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\SupplierRepositoryInterface;


class SupplierAPIController extends Controller
{
    protected $supplierRepository;

    public function __construct(SupplierRepositoryInterface $supplierRepository)
    {
        $this -> supplierRepository = $supplierRepository;
    }

    public function index()
    {
        try {
            $suppliers = $this->supplierRepository->all();
            return response()->json($suppliers);
        } catch (\Exception $e) {
            return response()->json(['error' =>  $e->getMessage()], 500);
        }
    }


    public function show($id){
        try{
            $supplier = $this -> supplierRepository->findById($id);
            return response() ->json($supplier);
        }catch(\Exception $e){
            return response() ->json(['error' => $e->getMessage()],500);
        }
    }

    public function store (Request $request){
        try{
            $this -> supplierRepository -> create($request);
            return response() -> json(['message' => 'Supplier created successfully'],200);
        }catch(\Exception $e){
            return response() -> json(['error' => $e -> getMessage()],500);
        }
    }

    public function update (Request $request , $id){
        try{
            $supplier = $this -> supplierRepository -> update($id , $request);
            return response() -> json (['message' => 'Supplier updated successfully' , 'supplier' => $supplier],200);
        } catch (\Exception $e){
            return response() ->json(['error' => $e -> getMessage()],500);
        }
    }

    public function destroy ($id){
        try{
            $this -> supplierRepository -> delete($id);
            return response() -> json(['message' => 'Supplier deleted successfully'] , 200);
        } catch (\Exception $e){
            return response() -> json(['error' => $e->getMessage()],500);
        }
    }

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
