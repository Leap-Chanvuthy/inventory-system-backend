<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\RawMaterial;
use App\Models\RawMaterialScrap;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Builder;

class RawMaterialScrapAPIController extends Controller
{

    private function allBuilder(): QueryBuilder
    {
        return QueryBuilder::for(RawMaterialScrap::class)
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('quantity'),
                AllowedFilter::exact('raw_material_id'),
                AllowedFilter::callback('search', function (Builder $query, $value) {
                    $query->where(function ($query) use ($value) {
                        $query->where('quantity', 'LIKE', "%{$value}%")
                            ->orWhere('reason', 'LIKE', "%{$value}%");
                    });
                })
            ])
            ->allowedSorts('created_at', 'updated_at' , 'quantity')
            ->defaultSort('-created_at');
    }

    private function allBuilderWithTrashed(): QueryBuilder
    {
        return QueryBuilder::for(RawMaterialScrap::class)
            ->onlyTrashed()
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('quantity'),
                AllowedFilter::exact('raw_material_id'),
                AllowedFilter::callback('search', function (Builder $query, $value) {
                    $query->where(function ($query) use ($value) {
                        $query->where('quantity', 'LIKE', "%{$value}%")
                            ->orWhere('reason', 'LIKE', "%{$value}%");
                    });
                })
            ])
            ->allowedSorts('created_at', 'updated_at' , 'quantity')
            ->defaultSort('-created_at');
    }


    public function index()
    {
        try {
            $stockScraps = $this -> allBuilder() -> with('raw_material')->paginate(10);
            return response()->json($stockScraps);
        }catch (Exception $e){
            return response() -> json(['error' => $e -> getMessage()],400);
        }
    }

    public function trashed()
    {
        try {
            $stockScraps = $this -> allBuilderWithTrashed() -> with('raw_material')->paginate(10);
            return response()->json($stockScraps);
        }catch (Exception $e){
            return response() -> json(['error' => $e -> getMessage()],400);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'raw_material_id' => 'required|exists:products,id',
                'quantity' => 'required|integer',
                'reason' => 'required|string',
            ]);

            if ($validated['raw_material_id']) {
                $product = RawMaterial::findOrFail($validated['raw_material_id']);
                $product->remaining_quantity -= $validated['quantity'];
                $product->save();
            }
    
            $stockScrap = RawMaterialScrap::create($validated);
    
            return response()->json(['message' => 'Stock scrap created successfully!' , $stockScrap],201);

        }catch(ValidationException $e){
            return response() -> json (['errors' => $e -> errors()],400);
        }catch (Exception $e){
            return response() -> json(['error' => $e -> getMessage()],400);
        }
    }

    public function show($id)
    {
        try {
            $stockScrap = RawMaterialScrap::with(['raw_material'])->findOrFail($id);
            return response()->json($stockScrap);
        }catch (Exception $e){
            return response() -> json(['error' => $e -> getMessage()],400);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'raw_material_id' => 'required|exists:products,id',
                'quantity' => 'required|integer',
                'reason' => 'required|string',
            ]);
    
            $stockScrap = RawMaterialScrap::findOrFail($id);
            
            if ($validated['raw_material_id']) {
                $rawMaterial = RawMaterial::findOrFail($validated['raw_material_id']);
    
                // Add back the old quantity
                $rawMaterial->remaining_quantity += $stockScrap->quantity;
    
                // Subtract the new quantity
                $rawMaterial->remaining_quantity -= $validated['quantity'];
    
                $rawMaterial->save();
            }

            $stockScrap->update($validated);
    
            return response()->json(['message' => 'Stock scrap deleted successfully!' , $stockScrap],200);

        }catch(ValidationException $e){
            return response() -> json (['errors' => $e -> errors()],400);
        }catch (Exception $e){
            return response() -> json(['error' => $e -> getMessage()],400);
        }
    }


    public function recover($id)
    {
        try {
            $stockScrap = RawMaterialScrap::onlyTrashed()->findOrFail($id);
            $stockScrap->restore();
            return response()->json(['message' => 'Stock scrap recovered successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }


    public function destroy($id)
    {
        try {
            $stockScrap = RawMaterialScrap::findOrFail($id);

            if ($stockScrap->raw_material_id) {
                $rawMaterial = RawMaterial::findOrFail($stockScrap->raw_material_id);
    
                $rawMaterial->remaining_quantity += $stockScrap->quantity;
    
                $rawMaterial->save();
            }
            $stockScrap -> quantity = 0;
            $stockScrap->save();

            $stockScrap->delete();
    
            return response()->json(['message' => 'Stock scrap deleted successfully!'],200);
        }catch (Exception $e){
            return response() -> json(['error' => $e -> getMessage()],400);
        }
    }
}
