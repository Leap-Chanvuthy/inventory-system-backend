<?php

namespace App\Http\Controllers\API;

use App\Exports\ProductExport;
use App\Exports\ProductScrapExport;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductScrap;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class ProductScrapAPIController extends Controller
{

    private function allBuilder(): QueryBuilder
    {
        return QueryBuilder::for(ProductScrap::class)
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('quantity'),
                AllowedFilter::exact('product_id'),
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
        return QueryBuilder::for(ProductScrap::class)
            -> onlyTrashed()
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('quantity'),
                AllowedFilter::exact('product_id'),
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
            $stockScraps = $this -> allBuilder() -> with('product.category')->paginate(10);
            return response()->json($stockScraps);
        }catch (Exception $e){
            return response() -> json(['error' => $e -> getMessage()],400);
        }
    }

    public function trashed()
    {
        try {
            $stockScraps = $this -> allBuilderWithTrashed() -> with('product')->paginate(10);
            return response()->json($stockScraps);
        }catch (Exception $e){
            return response() -> json(['error' => $e -> getMessage()],400);
        }
    }


    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer',
                'reason' => 'required|string',
            ]);

            if ($validated['product_id']) {
                $product = Product::findOrFail($validated['product_id']);
                $product->remaining_quantity -= $validated['quantity'];
                $product->save();
            }
    
            $stockScrap = ProductScrap::create($validated);
    
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
            $stockScrap = ProductScrap::with(['product.category'])->findOrFail($id);
            return response()->json($stockScrap);
        }catch (Exception $e){
            return response() -> json(['error' => $e -> getMessage()],400);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer',
                'reason' => 'required|string',
            ]);
    
            // $stockScrap = ProductScrap::findOrFail($id);

            // if ($validated['product_id']) {
            //     $product = Product::findOrFail($validated['product_id']);

            //     $product->remaining_quantity += $stockScrap -> quantity;

            //     $product->remaining_quantity -= $validated['quantity'];
            //     $product->save();
            // }
            
            // $stockScrap->update($validated);
    
            // return response()->json(['message' => 'Stock scrap deleted successfully!' , $stockScrap],200);

            $stockScrap = ProductScrap::findOrFail($id);
            $oldProduct = Product::findOrFail($stockScrap->product_id);
    
            $oldProduct->remaining_quantity += $stockScrap->quantity;
            $oldProduct->save();
    
            $newProduct = Product::findOrFail($validated['product_id']);
    
            if ($validated['quantity'] > $newProduct->remaining_quantity) {
                throw new \Exception("Quantity of product ID {$newProduct->id} is not enough.");
            }
    
            $newProduct->remaining_quantity -= $validated['quantity'];
            $newProduct->save();
    
            $stockScrap->update($validated);
    
            return response()->json(['message' => 'Stock scrap updated successfully!', 'stockScrap' => $stockScrap], 200);

        }catch(ValidationException $e){
            return response() -> json (['errors' => $e -> errors()],400);
        }catch (Exception $e){
            return response() -> json(['error' => $e -> getMessage()],400);
        }
    }

    
    public function recover($id)
    {
        try {
            $stockScrap = ProductScrap::onlyTrashed()->findOrFail($id);
            $stockScrap->restore();
            return response()->json(['message' => 'Stock scrap recovered successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }


    public function destroy($id)
    {
        try {
            $stockScrap = ProductScrap::findOrFail($id);
            
            if ($stockScrap-> product_id) {
                $product = Product::findOrFail($stockScrap-> product_id);
    
                $product->remaining_quantity += $stockScrap->quantity;
    
                $product->save();
            }

            $stockScrap -> quantity = 0;
            $stockScrap->save();

            $stockScrap->delete();
    
            return response()->json(['message' => 'Stock scrap deleted successfully!'],200);
        }catch (Exception $e){
            return response() -> json(['error' => $e -> getMessage()],400);
        }
    }


    public function export(Request $request)
    {
        try {
            $filters = $request->all();
            return Excel::download(new ProductScrapExport($request), 'product_scrap.xlsx');
        }  catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            return response()->json(['errors' => $e->failures()], 422); 
        }  catch (\Exception $e) {
            return response()->json(['error' => 'Export failed: ' . $e->getMessage()], 500);
        }
    }



}
