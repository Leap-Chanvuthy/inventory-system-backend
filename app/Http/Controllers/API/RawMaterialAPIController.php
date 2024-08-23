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
           ->allowedIncludes(['suppliers', 'products'])
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
            'image' => 'nullable|string|max:255',
            'unit_price' => 'required|numeric',
            'total_value' => 'required|numeric',
            'minimum_stock_level' => 'required|integer',
            'unit' => 'required|string|max:100',
            'package_size' => 'required|string|max:100',
            'supplier_id' => 'required|exists:suppliers,id',
            'product_id' => 'nullable|exists:products,id'
        ];

        if ($id) {
            $rules['name'] = 'sometimes|required|string|max:50';
        }

        $validatedData = $request->validate($rules);

        return $validatedData;
    }

    // Index method to list raw materials
    public function index() {
        $raw_materials = $this->allBuilder()->paginate(10);
       
        if (!$raw_materials) {
            return response()->json(['message' => 'No raw materials found'], 400);
        }

        return response()->json(['data' => $raw_materials], 200);
    }

    // Store method to create a new raw material
    public function store(Request $request) {
        $data = $this->validateAndExtractData($request);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('images', 'public');
            $data['image'] = $path;
        }

        $rawMaterial = RawMaterial::create($data);

        return response()->json(['message' => 'Raw material created successfully', 'data' => $rawMaterial], 201);
    }

    // Update method to modify an existing raw material
    public function update(Request $request, $id) {
        $data = $this->validateAndExtractData($request, $id);

        $rawMaterial = RawMaterial::find($id);

        if (!$rawMaterial) {
            return response()->json(['message' => 'Raw material not found'], 404);
        }

        if ($request->hasFile('image')) {
            if ($rawMaterial->image && Storage::exists($rawMaterial->image)) {
                Storage::delete($rawMaterial->image);
            }

            $path = $request->file('image')->store('images', 'public');
            $data['image'] = $path;
        }

        $rawMaterial->update($data);

        return response()->json(['message' => 'Raw material updated successfully', 'data' => $rawMaterial], 200);
    }

    // Destroy method to delete a raw material
    public function destroy($id) {
        $rawMaterial = RawMaterial::find($id);

        if (!$rawMaterial) {
            return response()->json(['message' => 'Raw material not found'], 404);
        }

        if ($rawMaterial->image && Storage::exists($rawMaterial->image)) {
            Storage::delete($rawMaterial->image);
        }

        $rawMaterial->delete();

        return response()->json(['message' => 'Raw material deleted successfully'], 200);
    }

    public function export(Request $request)
    {
        $filters = $request->all(); // Get all filters from the request

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
