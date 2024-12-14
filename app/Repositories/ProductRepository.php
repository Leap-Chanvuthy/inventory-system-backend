<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\ProductRawMaterial;
use App\Models\RawMaterial;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Expr\Throw_;

class ProductRepository implements ProductRepositoryInterface
{
    protected $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * Build query with filters and includes
     * @return QueryBuilder
     */
    private function allBuilder(): QueryBuilder
    {
        return QueryBuilder::for(Product::class)
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('product_category_id'),
                AllowedFilter::exact('status'),
                AllowedFilter::callback('search', function (Builder $query, $value) {
                    $query->where(function ($query) use ($value) {
                        $query->where('product_name', 'LIKE', "%{$value}%")
                            ->orWhere('product_code', 'LIKE', "%{$value}%")
                            ->orWhere('unit_price_in_usd', 'LIKE', "%{$value}%")
                            ->orWhere('total_value_in_usd', 'LIKE', "%{$value}%")
                            ->orWhere('warehouse_location', 'LIKE', "%{$value}%")
                            ->orWhere('status', 'LIKE', "%{$value}%");
                    });
                }),
            ])
            ->allowedSorts('created_at', 'updated_at', 'product_name')
            ->defaultSort('-created_at');
    }

    private function allBuilderWithTrashed(): QueryBuilder
    {
        return QueryBuilder::for(Product::class)
            ->onlyTrashed()
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('product_category_id'),
                AllowedFilter::exact('status'),
                AllowedFilter::callback('search', function (Builder $query, $value) {
                    $query->where(function ($query) use ($value) {
                        $query->where('product_name', 'LIKE', "%{$value}%")
                            ->orWhere('product_code', 'LIKE', "%{$value}%")
                            ->orWhere('unit_price_in_usd', 'LIKE', "%{$value}%")
                            ->orWhere('total_value_in_usd', 'LIKE', "%{$value}%")
                            ->orWhere('warehouse_location', 'LIKE', "%{$value}%")
                            ->orWhere('status', 'LIKE', "%{$value}%");
                    });
                }),
            ])
            ->allowedSorts('created_at', 'updated_at', 'product_name');
    }


    private function validateAndExtractData(Request $request, $id = null): array
    {
        $rules = [
            'staging_date' => 'nullable|date',
            'product_name' => 'required|string|max:255',
            'image.*' => 'nullable|image|mimes:jpeg,png,jpg|max:10000',
            'quantity' => 'required|integer|min:0',
            'remaining_quantity' => 'required|integer|min:0',
            'minimum_stock_level' => 'required|integer|min:0',
            'unit_of_measurement' => 'required|string|max:255',
            'package_size' => 'nullable|string|max:255',
            'warehouse_location' => 'nullable|string|max:255',
            'unit_price_in_usd' => 'required|numeric|min:0',
            'total_value_in_usd' => 'required|numeric|min:0',
            'exchange_rate_from_usd_to_riel' => 'required|numeric|min:0',
            'unit_price_in_riel' => 'required|numeric|min:0',
            'total_value_in_riel' => 'required|numeric|min:0',
            'exchange_rate_from_riel_to_usd' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|string|max:255',
            'barcode' => 'nullable|string|max:255',
            'product_category_id' => 'required|exists:product_categories,id',
            'raw_materials' => 'required|array',
            'raw_materials.*.id' => 'required|integer|exists:raw_materials,id',
            'raw_materials.*.quantity_used' => 'required|numeric|min:0',
        ];

        $validatedData = $request->validate($rules);

        return $validatedData;
    }


    // public function generateProductCode(): string
    // {
    //     $lastProduct = Product::withTrashed()
    //         ->selectRaw('MAX(CAST(SUBSTRING(product_code, 5) AS UNSIGNED)) AS max_code')
    //         ->first();

    //     $lastCode = $lastProduct->max_code ?? 0;

    //     $newNumber = str_pad($lastCode + 1, 6, '0', STR_PAD_LEFT);
    //     return 'PRODUCT-' . $newNumber;
    // }

    public function generateProductCode(): string
    {
        return DB::transaction(function () {
            $lastProduct = Product::withTrashed()
                ->selectRaw('MAX(CAST(SUBSTRING(product_code, 9) AS UNSIGNED)) AS max_code')
                ->lockForUpdate()
                ->first();
    
            $lastCode = $lastProduct->max_code ?? 0;
    
            $newNumber = str_pad($lastCode + 1, 6, '0', STR_PAD_LEFT);
            return 'PRODUCT-' . $newNumber;
        });
    }



    public function all(): LengthAwarePaginator
    {
        return $this->allBuilder()->with('product_images' , 'category' , 'raw_materials' )->paginate(10);
    }


    public function trashed(): LengthAwarePaginator {
        return $this -> allBuilderWithTrashed() ->with('product_images' , 'category') -> paginate (10);
    }


    public function findById($id): Product {
        return $this -> product -> with('product_images' , 'category' , 'raw_materials.category') -> findOrFail($id) ;
    }


    public function create(Request $request): Product
    {
        $data = $this->validateAndExtractData($request);
        $data['product_code'] = $this->generateProductCode();

        $product = Product::create($data);

        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $file) {
                $fileName = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('products', $fileName, 'public');
                
                $product->product_images()->create(['image' => $path]);
            }
        }

        if (!empty($data['raw_materials'])) {
            foreach ($data['raw_materials'] as $rawMaterial) {

                $rawMaterialModel = RawMaterial::find($rawMaterial['id']);

                if (!$rawMaterialModel) {
                    return response()->json([
                        'error' => 'Raw material not found.',
                    ], 404);
                }

                if ($rawMaterial['quantity_used'] > $rawMaterialModel->remaining_quantity) {
                    throw new Exception("Raw material id {$rawMaterial['id']} with inputed quantity ({$rawMaterial['quantity_used']}) cannot be greater the remaining quantity ({$rawMaterialModel->remaining_quantity}).");
                }

                $productRawMaterial = new ProductRawMaterial();
                $productRawMaterial->product_id = $product->id;
                $productRawMaterial->raw_material_id = $rawMaterial['id'];
                $productRawMaterial->quantity_used = $rawMaterial['quantity_used'];

                $productRawMaterial->save();

                $rawMaterialModel->remaining_quantity -= $rawMaterial['quantity_used'];
                $rawMaterialModel->save();
            }
        }

        return $product;
    }



    public function update($id, Request $request): Product
    {
        $data = $this->validateAndExtractData($request);

        $product = Product::find($id);

        if ($request->hasFile('image')) {
            foreach ($product->product_images as $image) {
                if (Storage::disk('public')->exists($image->image)) {
                    Storage::disk('public')->delete($image->image);
                }
                $image->delete();
            }
            foreach ($request->file('image') as $file) {
                $fileName = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('products', $fileName, 'public');
                
                $product->product_images()->create(['image' => $path]);
            }
        }

        if (!$product) {
            return response()->json([
                'error' => 'Product not found.',
            ], 404);
        }

        $previousRawMaterials = $product->raw_materials;

        $product->update($data);

        if (!empty($data['raw_materials'])) {
            foreach ($data['raw_materials'] as $rawMaterial) {

                $rawMaterialModel = RawMaterial::find($rawMaterial['id']);

                if (!$rawMaterialModel) {
                    return response()->json([
                        'error' => 'Raw material not found.',
                    ], 404);
                }

                $productRawMaterial = ProductRawMaterial::where('product_id', $product->id)
                    ->where('raw_material_id', $rawMaterial['id'])
                    ->first();

                $previousQuantityUsed = 0;
                if ($productRawMaterial) {
                    $previousQuantityUsed = $productRawMaterial->quantity_used;
                    $rawMaterialModel->remaining_quantity += $previousQuantityUsed;
                }

                if ($rawMaterial['quantity_used'] > $rawMaterialModel->remaining_quantity) {
                    throw new Exception("Raw material id {$rawMaterial['id']} with inputed quantity ({$rawMaterial['quantity_used']}) cannot be greater the remaining quantity ({$rawMaterialModel->remaining_quantity}).");
                }

                if ($productRawMaterial) {
                    $productRawMaterial->quantity_used = $rawMaterial['quantity_used'];
                    $productRawMaterial->save();
                } else {
                    ProductRawMaterial::create([
                        'product_id' => $product->id,
                        'raw_material_id' => $rawMaterial['id'],
                        'quantity_used' => $rawMaterial['quantity_used'],
                    ]);
                }

                $rawMaterialModel->remaining_quantity -= $rawMaterial['quantity_used'];
                $rawMaterialModel->save();
            }
        }

        // Step 1: Loop through previous raw materials associated with the product
        foreach ($previousRawMaterials as $previousRawMaterial) {
            $isUpdated = false;

            foreach ($data['raw_materials'] as $updatedRawMaterial) {
                if ($previousRawMaterial->id == $updatedRawMaterial['id']) {
                    $isUpdated = true;
                    break;
                }
            }

            if (!$isUpdated) {
                $productRawMaterial = ProductRawMaterial::where('product_id', $product->id)
                    ->where('raw_material_id', $previousRawMaterial->id)
                    ->first();

                if ($productRawMaterial) {

                    $rawMaterialModel = RawMaterial::find($previousRawMaterial->id);
                    if ($rawMaterialModel) {
                        $rawMaterialModel->remaining_quantity += $productRawMaterial->quantity_used;
                        $rawMaterialModel->save();
                    }
                }

                ProductRawMaterial::where('product_id', $product->id)
                    ->where('raw_material_id', $previousRawMaterial->id)
                    ->delete();
            }
        }

        return $product;
    }







    public function delete(int $id): void {
        $product = Product::findOrFail($id);

        foreach ($product->raw_materials as $rawMaterial) {
            $rawMaterial->remaining_quantity += $rawMaterial->pivot->quantity_used;
            $rawMaterial->save();
        }

        $product->raw_materials()->detach();
        $product->delete();

    }
}
