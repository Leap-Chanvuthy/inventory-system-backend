<?php

namespace App\Repositories;

use App\Models\RawMaterial;
use App\Repositories\Interfaces\RawMaterialRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Builder;

class RawMaterialRepository implements RawMaterialRepositoryInterface
{
    protected $rawMaterial;

    public function __construct(RawMaterial $rawMaterial)
    {
        $this->rawMaterial = $rawMaterial;
    }

    /**
     * Build query with filters and includes
     * @return QueryBuilder
     */
    private function allBuilder(): QueryBuilder
    {
        return QueryBuilder::for(RawMaterial::class)
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('raw_material_category_id'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('minimum_stock_level'),
                AllowedFilter::callback('search', function (Builder $query, $value) {
                    $query->where(function ($query) use ($value) {
                        $query->where('name', 'LIKE', "%{$value}%")
                            ->orWhere('material_code', 'LIKE', "%{$value}%")
                            ->orWhere('unit_price_in_riel', 'LIKE', "%{$value}%")
                            ->orWhere('total_value_in_riel', 'LIKE', "%{$value}%")
                            ->orWhere('unit_price_in_usd', 'LIKE', "%{$value}%")
                            ->orWhere('total_value_in_usd', 'LIKE', "%{$value}%")
                            ->orWhere('location', 'LIKE', "%{$value}%")
                            ->orWhere('status', 'LIKE', "%{$value}%");
                    });
                })
            ])
            ->allowedSorts('created_at', 'updated_at' , 'material_code')
            ->defaultSort('-created_at');
    }

    private function allBuilderWithTrashed(): QueryBuilder
    {
        return QueryBuilder::for(RawMaterial::class)
            ->onlyTrashed() 
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('raw_material_category'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('minimum_stock_level'),
                AllowedFilter::callback('search', function (Builder $query, $value) {
                    $query->where(function ($query) use ($value) {
                        $query->where('name', 'LIKE', "%{$value}%")
                            ->orWhere('material_code', 'LIKE', "%{$value}%")
                            ->orWhere('location', 'LIKE', "%{$value}%")
                            ->orWhere('status', 'LIKE', "%{$value}%");
                    });
                })
            ])
            ->allowedSorts('created_at', 'updated_at' , 'material_code')
            ->defaultSort('-created_at');
    }

    
    public function generateRawMaterialCode(): string
    {
        $lastMaterial = RawMaterial::withTrashed()
            ->selectRaw('MAX(CAST(SUBSTRING(material_code, 5) AS UNSIGNED)) AS max_code')
            ->first();

        $lastCode = $lastMaterial->max_code ?? 0;

        $newNumber = str_pad($lastCode + 1, 6, '0', STR_PAD_LEFT);
        return 'MAT-' . $newNumber;
    }


    public function all(): LengthAwarePaginator
    {
        return $this->allBuilder()->with('raw_material_images' ,'category' , 'supplier')->paginate(10);
    }

    public function allWithoutInvoice(): LengthAwarePaginator
    {
        return $this->allBuilder() -> withTrashed() ->with('raw_material_images' ,'category' , 'supplier') -> whereDoesntHave('purchase_invoice_details')->paginate(10);
    }

    public function allWithoutSupplier(): LengthAwarePaginator
    {
        return $this->allBuilder()->with('raw_material_images' ,'category') -> whereDoesntHave('supplier')->paginate(10);
    }


    public function trashed(): LengthAwarePaginator
    {
        return $this -> allBuilderWithTrashed() ->with('category') -> paginate (10);
    }


    public function findById(int $id): RawMaterial
    {
        return RawMaterial::with('supplier' , 'raw_material_images' , 'category')->findOrFail($id);
    }




    public function create(Request $request): RawMaterial
    {

        $data = $this -> validateAndExtractData($request);
        $data['material_code'] = $this -> generateRawMaterialCode();

        $rawMaterial = RawMaterial::create($data);
        
        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $file) {
                $fileName = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('raw_materials', $fileName, 'public');
                
                $rawMaterial->raw_material_images()->create(['image' => $path]);
            }
        }

        return $rawMaterial;
    }


    public function update(int $id, Request $request): RawMaterial
    {
        $data = $this->validateAndExtractData($request);

        $rawMaterial = RawMaterial::with('raw_material_images') -> findOrFail($id);

        $rawMaterial->update($data);

        if ($request->hasFile('image')) {
            foreach ($rawMaterial->raw_material_images as $image) {
                if (Storage::disk('public')->exists($image->image)) {
                    Storage::disk('public')->delete($image->image);
                }
                $image->delete();
            }
            foreach ($request->file('image') as $file) {
                $fileName = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('raw_materials', $fileName, 'public');
                
                $rawMaterial->raw_material_images()->create(['image' => $path]);
            }
        }

        return $rawMaterial;
    }


    public function delete(int $id): void
    {
        $rawMaterial = RawMaterial::findOrFail($id);

        foreach ($rawMaterial->raw_material_images as $image) {
            if ($image->image && Storage::disk('public')->exists($image->image)) {
                Storage::disk('public')->delete($image->image);
            }
            $image->delete();
        }

        $rawMaterial->delete();
    }


    private function validateAndExtractData(Request $request, $id = null): array
    {
        $rules = [
            'name' => 'required|string|max:50',
            // 'material_code' => 'required|string|max:255',
            'supplier_id' => 'required|exists:suppliers,id',
            'quantity' => 'required|integer',
            'remaining_quantity' => 'required|integer',
            'unit_price_in_usd' => 'required|numeric',
            'total_value_in_usd' => 'required|numeric',
            'exchange_rate_from_usd_to_riel' => 'required|numeric',
            'unit_price_in_riel' => 'required|numeric',
            'total_value_in_riel' => 'required|numeric',
            'exchange_rate_from_riel_to_usd' => 'required|numeric',
            'minimum_stock_level' => 'required|integer',
            'status' => 'required|string|max:100',
            'unit_of_measurement' => 'required|string|max:100',
            'package_size' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'expiry_date' => 'nullable|date',
            'image.*' => 'nullable|image|mimes:jpeg,png,jpg|max:10000',
            'raw_material_category_id' => 'required|exists:raw_material_categories,id'
        ];

        $validatedData = $request->validate($rules);

        return $validatedData;
    }

}
