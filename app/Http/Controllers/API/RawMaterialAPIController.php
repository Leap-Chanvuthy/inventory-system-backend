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
use App\Services\RawMaterialService;

class RawMaterialAPIController extends Controller
{
    protected $rawMaterialRepository;
    protected $purchaseInvoiceRepository;
    protected $purchaseInvoiceDetailRepository;
    protected $rawMaterialService;

    public function __construct(RawMaterialService $rawMaterialService ,RawMaterialRepositoryInterface $rawMaterialRepository , PurchaseInvoiceRepositoryInterface $purchaseInvoiceRepository , PurchaseInvoiceDetailRepositoryInterface $purchaseInvoiceDetailRepository)
    {
        $this->rawMaterialRepository = $rawMaterialRepository;
        $this -> purchaseInvoiceDetailRepository = $purchaseInvoiceRepository;
        $this -> purchaseInvoiceDetailRepository = $purchaseInvoiceDetailRepository;
        $this -> rawMaterialService = $rawMaterialService;
    }

    public function index()
    {
        return $this->rawMaterialRepository->all();
    }

    public function show($id)
    {
        return $this->rawMaterialRepository->findById($id);
    }

    // public function store(Request $request)
    // {
    //     try {
    //         $rawMaterial = $this->rawMaterialRepository->create($request);
    //         return response()->json(['message' => 'Raw material created successfully', 'data' => $rawMaterial], 201);
    //     } catch (ValidationException $e) {
    //         return response()->json(['errors' => $e->errors()], 422);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage()], 500);
    //     }
    // }


    // public function store(Request $request)
    // {
    //     $validatedData = $request->validate([
    //         'raw_materials.*.name' => 'required|string|max:50',
    //         'raw_materials.*.quantity' => 'required|integer',
    //         'raw_materials.*.unit_price' => 'required|numeric',
    //         'raw_materials.*.total_value' => 'required|numeric',
    //         'raw_materials.*.minimum_stock_level' => 'required|integer',
    //         'raw_materials.*.unit' => 'required|string|max:100',
    //         'raw_materials.*.package_size' => 'required|string|max:100',
    //         'raw_materials.*.supplier_id' => 'required|exists:suppliers,id',
    //         'raw_materials.*.product_id' => 'nullable|exists:products,id',
    //     ]);

    //     // Call the service method to create raw materials and related records
    //     $result = $this->rawMaterialService->createRawMaterialsWithInvoice($validatedData['raw_materials']);

    //     return response()->json($result, 201);
    // }



    // public function store(Request $request)
    // {
    //     try{
    //         $validatedData = $request->validate([
    //             'raw_materials.*.name' => 'required|string|max:50',
    //             'raw_materials.*.quantity' => 'required|integer',
    //             'raw_materials.*.image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    //             'raw_materials.*.unit_price' => 'required|numeric',
    //             'raw_materials.*.total_value' => 'required|numeric',
    //             'raw_materials.*.minimum_stock_level' => 'required|integer',
    //             'raw_materials.*.unit' => 'required|string|max:100',
    //             'raw_materials.*.package_size' => 'required|string|max:100',
    //             'raw_materials.*.supplier_id' => 'required|exists:suppliers,id',
    //             'raw_materials.*.product_id' => 'nullable|exists:products,id',
    //             'discount_percentage' => 'nullable|numeric', 
    //             'tax_percentage' => 'nullable|numeric',       
    //         ]);
        
    //         $discountPercentage = $request->input('discount_percentage', 0);
    //         $taxPercentage = $request->input('tax_percentage', 0);
        
    //         $result = $this->rawMaterialService->createRawMaterialsWithInvoice(
    //             $validatedData['raw_materials'],
    //             $discountPercentage,
    //             0,
    //             $taxPercentage,
    //             0   
    //         );
        
    //         return response()->json($result, 201);
    //     }catch (ValidationException $e) {
    //         return response()->json(['errors' => $e->errors()], 422);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage()], 500);
    //     }
    // }


    public function store(Request $request)
{
    try {
        // Validate the request data
        $validatedData = $request->validate([
            'raw_materials.*.name' => 'required|string|max:50',
            'raw_materials.*.quantity' => 'required|integer',
            'raw_materials.*.image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'raw_materials.*.unit_price' => 'required|numeric',
            'raw_materials.*.total_value' => 'required|numeric',
            'raw_materials.*.minimum_stock_level' => 'required|integer',
            'raw_materials.*.unit' => 'required|string|max:100',
            'raw_materials.*.package_size' => 'required|string|max:100',
            'raw_materials.*.supplier_id' => 'required|exists:suppliers,id',
            'raw_materials.*.product_id' => 'nullable|exists:products,id',
            'discount_percentage' => 'nullable|numeric',
            'tax_percentage' => 'nullable|numeric',
        ]);

        $discountPercentage = $request->input('discount_percentage', 0);
        $taxPercentage = $request->input('tax_percentage', 0);

        // Extract raw materials data and handle image storage
        $rawMaterialsData = $validatedData['raw_materials'];
        $processedRawMaterialsData = [];

        foreach ($rawMaterialsData as $data) {
            // Check if an image is provided
            if (isset($data['image'])) {
                $file = $data['image'];
                $fileName = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('raw_materials', $fileName, 'public');
                $data['image'] = $path; // Save the path of the stored image
            }

            $processedRawMaterialsData[] = $data; // Add the processed data to the array
        }

        // Call the service method to create raw materials and generate the invoice
        $result = $this->rawMaterialService->createRawMaterialsWithInvoice(
            $processedRawMaterialsData,
            $discountPercentage,
            0,
            $taxPercentage,
            0
        );

        return response()->json($result, 201);
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
