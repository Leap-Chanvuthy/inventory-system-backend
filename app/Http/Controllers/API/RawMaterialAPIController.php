<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException ;
use App\Imports\RawMaterialImport;
use App\Exports\RawMaterialExport;
use App\Repositories\Interfaces\PurchaseInvoiceDetailRepositoryInterface;
use App\Repositories\Interfaces\PurchaseInvoiceRepositoryInterface;
use Maatwebsite\Excel\Facades\Excel;
use App\Repositories\Interfaces\RawMaterialRepositoryInterface;


class RawMaterialAPIController extends Controller
{
    protected $rawMaterialRepository;
    protected $purchaseInvoiceRepository;
    protected $purchaseInvoiceDetailRepository;

    public function __construct(RawMaterialRepositoryInterface $rawMaterialRepository , PurchaseInvoiceRepositoryInterface $purchaseInvoiceRepository , PurchaseInvoiceDetailRepositoryInterface $purchaseInvoiceDetailRepository)
    {
        $this->rawMaterialRepository = $rawMaterialRepository;
        $this -> purchaseInvoiceDetailRepository = $purchaseInvoiceRepository;
        $this -> purchaseInvoiceDetailRepository = $purchaseInvoiceDetailRepository;
    }

    public function index()
    {
        return $this->rawMaterialRepository->all();
    }

    public function show($id)
    {
        return $this->rawMaterialRepository->findById($id);
    }

    public function store(Request $request)
    {
        try {
            $rawMaterial = $this->rawMaterialRepository->create($request);
            return response()->json(['message' => 'Raw material created successfully', 'data' => $rawMaterial], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    public function update(Request $request, $id)
    {
        try {
            $rawMaterial = $this->rawMaterialRepository->update($id, $request);
            return response()->json(['message' => 'Raw material updated successfully', 'data' => $rawMaterial], 200);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }    

    public function destroy($id)
    {
        $this->rawMaterialRepository->delete($id);
        return response()->json(['message' => 'Raw material deleted successfully'], 200);
    }

    

    public function export(Request $request)
    {
        $filters = $request->all();

        return Excel::download(new RawMaterialExport($filters), 'raw_materials.xlsx');
    }


    public function import(Request $request)
    {
        $request->validate([
            'raw_material_file' => 'required|file|mimes:xlsx,csv'
        ]);

        $file = $request->file('raw_material_file');
        Excel::import(new RawMaterialImport, $file);

        return response()->json(['message' => 'Raw materials imported successfully'], 200);
    }

}
