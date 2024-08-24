<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\RawMaterial;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Support\Facades\Storage;
use App\Imports\RawMaterialImport;
use App\Exports\RawMaterialExport;
use Maatwebsite\Excel\Facades\Excel;

class RawMaterialAPIController extends Controller
{
    // Method to build the query for listing raw materials
    private function allBuilder() : QueryBuilder {
        return QueryBuilder::for(RawMaterial::class)
           ->allowedIncludes(['supplier', 'product'])
           ->allowedFilters([
              AllowedFilter::exact('id'),
              AllowedFilter::exact('name'),
              AllowedFilter::exact('quantity'),
              AllowedFilter::exact('unit_price'),
              AllowedFilter::exact('total_value'),
              AllowedFilter::exact('minimum_stock_level'),
              AllowedFilter::exact('unit'),
              AllowedFilter::exact('package_size'),
           ])
           ->allowedSorts('created_at', 'quantity', 'package_size', 'total_value', 'minimum_stock_level')
           ->defaultSort('-created_at');
    }

    // Reusable class for validation and data extraction
    private function validateAndExtractData(Request $request, $id = null)
    {
        $rules = [
            'name' => 'required|string|max:50',
            'quantity' => 'required|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'unit_price' => 'required|numeric',
            'total_value' => 'required|numeric',
            'minimum_stock_level' => 'required|integer',
            'unit' => 'required|string|max:100',
            'package_size' => 'required|string|max:100',
            'supplier_id' => 'required|exists:suppliers,id',
            'product_id' => 'nullable|exists:products,id'
        ];

        $validatedData = $request->validate($rules);

        return $validatedData;
    }

    // Index method to list raw materials
    public function index() {
        // $raw_materials = RawMaterial::with('supplier')->paginate(10);
        $raw_materials = $this->allBuilder() ->with('supplier' , 'product') -> paginate(10);
       
        if (!$raw_materials) {
            return response()->json(['message' => 'No raw materials found'], 400);
        }

        return response()->json(['data' => $raw_materials], 200);
    }


    // create a new raw materials
    public function store(Request $request)
    {
        try {
            $data = $this->validateAndExtractData($request);
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('raw_materials', $fileName, 'public');  
                $data['image'] = $path;
            }
            $rawMaterial = RawMaterial::create($data);
            return response()->json(['message' => 'Raw material created successfully', 'data' => $rawMaterial], 201);
        }catch (\Exception $e){
            return response() -> json(['error' => $e -> getMessage()],500);
        }
    }
    

    // Update method to modify an existing raw material
    public function update(Request $request, $id)
    {
        try {
            $validatedData = $this->validateAndExtractData($request, $id);

            $rawMaterial = RawMaterial::find($id);
    
            if (!$rawMaterial) {
                return response()->json(['message' => 'Raw material not found'], 404);
            }
    
            if ($request->hasFile('image')) {
                if ($rawMaterial->image && Storage::exists($rawMaterial->image)) {
                    Storage::delete($rawMaterial->image);
                }

                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $path = $image->storeAs('raw_materials', $imageName, 'public');
    
                $validatedData['image'] = $path;
            }
    
            $rawMaterial->update($validatedData);
    
            return response()->json(['message' => 'Raw material updated successfully', 'data' => $rawMaterial], 200);
        
        }catch (\Exception $e){
            return response() -> json(['error' => $e -> getMessage()],500);
        }
    }


    // Destroy method to delete a raw material
    public function destroy($id) {
        $rawMaterial = RawMaterial::find($id);
    
        if (!$rawMaterial) {
            return response()->json(['message' => 'Raw material not found'], 404);
        }
    
        if ($rawMaterial->image && Storage::disk('public')->exists($rawMaterial->image)) {
            Storage::disk('public')->delete($rawMaterial->image);
        }
    
        $rawMaterial->delete();
    
        return response()->json(['message' => 'Raw material deleted successfully'], 200);
    }
    

    // export record from database 
    public function export(Request $request)
    {
        $filters = $request->all();

        return Excel::download(new RawMaterialExport($filters), 'raw_materials.xlsx');
    }

    // bulk upload to database
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
