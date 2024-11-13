<?php

namespace App\Http\Controllers\API;

use App\Imports\SupplierImport;
use App\Exports\SupplierExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\UploadedFile;
use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Repositories\Interfaces\SupplierRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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


    public function store(Request $request)
    {
        try {
            $supplier = $this->supplierRepository->create($request);
            return response()->json(['message' => 'Supplier created successfully', 'supplier' => $supplier], 200);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $supplier = $this->supplierRepository->update($request, $id);
            return response()->json(['message' => 'Supplier updated successfully', 'supplier' => $supplier], 200);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
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

    // stats
    public function getSupplierStats()
    {
        try{
            $statusData = Supplier::selectRaw('supplier_status, COUNT(*) as count')
            ->groupBy('supplier_status')
            ->get();

            $categoryData = Supplier::selectRaw('supplier_category, COUNT(*) as count')
                        ->groupBy('supplier_category')
                        ->get();

            return response()->json([
            'supplier_status' => $statusData,
            'supplier_category' => $categoryData,
            ]);
        } catch (\Exception $e){
            return response() -> json(['error' => $e->getMessage(),500]);
        }
    }

    // get top 10 suppliers that supplier most raw material
    public function topSuppliers(): JsonResponse
    {
        $suppliers = Supplier::with(['raw_materials'])
            ->get()
            ->map(function ($supplier) {
                $rawMaterialSupplied = $supplier->raw_materials->unique('id')->count(); 
    
                return [
                    'supplier_info' => [
                        'id' => $supplier->id,
                        'name' => $supplier->name,
                        'email' => $supplier->email,
                        'phone_number' => $supplier->phone_number,
                    ],
                    'raw_material_supplied' => $rawMaterialSupplied,
                ];
            })
            ->filter(function ($supplier) {
                return $supplier['raw_material_supplied'] > 0;
            })
            ->sortByDesc('raw_material_supplied')
            ->take(10); 
    
        return response()->json([
            'top_suppliers' => $suppliers,
        ]);
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
        }catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            return response()->json(['errors' => $e->failures()], 422); 
        }
        // catch (\Exception $e) {
        //     return response()->json(['error' =>  $e->getMessage()], 500);
        // }
    }

    public function export(Request $request)
    {
        try {
            $filters = $request->all();
            return Excel::download(new SupplierExport($filters), 'suppliers.xlsx');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error exporting suppliers: ' . $e->getMessage()], 500);
        }
    }
}
