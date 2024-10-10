<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductRawMaterial;
use App\Models\RawMaterial;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductAPIController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'date' => 'nullable|date',
            'description' => 'required|string|max:255',
            'quantity' => 'required|integer',
            'cost_per_item' => 'required|numeric',
            'total_value' => 'required|numeric',
            'category' => 'required|string',
            'unit' => 'required|string',
            'raw_materials' => 'required|array',
            'raw_materials.*.id' => 'required|integer|exists:raw_materials,id',
            'raw_materials.*.quantity_used' => 'required|integer|min:1'
        ]);
    
        try {

            $product = Product::create([
                'item_name' => $validated['item_name'],
                'description' => $validated['description'],
                'quantity' => $validated['quantity'],
                'cost_per_item' => $validated['cost_per_item'],
                'total_value' => $validated['total_value'],
                'category' => $validated['category'],
                'unit' => $validated['unit'],
                'date' => $validated['date'],
            ]);
    
            $rawMaterialsToUpdate = [];
    
            foreach ($validated['raw_materials'] as $material) {
                $rawMaterial = RawMaterial::find($material['id']);
    
                if ($rawMaterial->quantity < $material['quantity_used']) {
                    throw new \Exception('Insufficient stock for ' . $rawMaterial->name);
                }
    
                $rawMaterialsToUpdate[] = [
                    'raw_material_id' => $rawMaterial->id,
                    'quantity_used' => $material['quantity_used']
                ];
            }
    
            foreach ($rawMaterialsToUpdate as $materialToUpdate) {
                $rawMaterial = RawMaterial::find($materialToUpdate['raw_material_id']);
                $rawMaterial->quantity -= $materialToUpdate['quantity_used'];
                $rawMaterial->save();

                ProductRawMaterial::create([
                    'product_id' => $product->id,
                    'raw_material_id' => $rawMaterial->id,
                    'quantity_used' => $materialToUpdate['quantity_used'],
                ]);
            }

    
            return response()->json([
                'message' => 'Product created successfully',
                'product' => $product
            ], 201);
    
        }catch (ValidationException $e){
            return response() -> json(['errors' => $e -> errors()],400);
        }
        catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create product: ' . $e->getMessage()
            ], 400);
        }
    }




    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
    
        $validated = $request->validate([
            'item_name' => 'sometimes|required|string|max:255',
            'date' => 'nullable|date',
            'description' => 'sometimes|required|string|max:255',
            'quantity' => 'sometimes|required|integer',
            'cost_per_item' => 'sometimes|required|numeric',
            'total_value' => 'sometimes|required|numeric',
            'category' => 'sometimes|required|string',
            'unit' => 'sometimes|required|string',
            'raw_materials' => 'required|array',
            'raw_materials.*.id' => 'required|integer|exists:raw_materials,id',
            'raw_materials.*.quantity_used' => 'required|integer|min:1'
        ]);
    
        try {
            $product->update([
                'item_name' => $validated['item_name'] ?? $product->item_name,
                'description' => $validated['description'] ?? $product->description,
                'quantity' => $validated['quantity'] ?? $product->quantity,
                'cost_per_item' => $validated['cost_per_item'] ?? $product->cost_per_item,
                'total_value' => $validated['total_value'] ?? $product->total_value,
                'category' => $validated['category'] ?? $product->category,
                'unit' => $validated['unit'] ?? $product->unit,
                'date' => $validated['date'] ?? $product->date,
            ]);
    
            foreach ($validated['raw_materials'] as $material) {
                $rawMaterial = RawMaterial::find($material['id']);

                if (!$rawMaterial) {
                    throw new \Exception("Raw material with ID {$material['id']} not found.");
                }
    
                if ($rawMaterial->quantity < $material['quantity_used']) {
                    throw new \Exception('Insufficient stock for ' . $rawMaterial->name);
                }
    
                $productRawMaterial = ProductRawMaterial::where('product_id', $product->id)
                    ->where('raw_material_id', $rawMaterial->id)
                    ->first();
    
                if ($productRawMaterial) {
                    $rawMaterial->quantity -= ($material['quantity_used'] - $productRawMaterial->quantity_used);
                    $rawMaterial->save();
    
                    $productRawMaterial->update([
                        'quantity_used' => $material['quantity_used']
                    ]);
                } else {
                    $rawMaterial->quantity -= $material['quantity_used'];
                    $rawMaterial->save();
    
                    ProductRawMaterial::create([
                        'product_id' => $product->id,
                        'raw_material_id' => $rawMaterial->id,
                        'quantity_used' => $material['quantity_used'],
                    ]);
                }
            }
    
            return response()->json([
                'message' => 'Product updated successfully',
                'product' => $product
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update product: ' . $e->getMessage()
            ], 400);
        }
    }
    


    

}
