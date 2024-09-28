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
            ->allowedIncludes(['supplier'])
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('raw_material_category'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('minimum_stock_level'),
                AllowedFilter::callback('search', function (Builder $query, $value) {
                    $query->where(function ($query) use ($value) {
                        $query->where('name', 'LIKE', "%{$value}%")
                            ->orWhere('material_code', 'LIKE', "%{$value}%")
                            ->orWhere('unit_price', 'LIKE', "%{$value}%")
                            ->orWhere('total_value', 'LIKE', "%{$value}%")
                            ->orWhere('location', 'LIKE', "%{$value}%")
                            ->orWhere('status', 'LIKE', "%{$value}%")
                            ->orWhere('raw_material_category', 'LIKE', "%{$value}%");
                    });
                })
            ])
            ->allowedSorts('created_at', 'updated_at')
            ->defaultSort('-created_at');
    }

    public function all(): LengthAwarePaginator
    {
        return $this->allBuilder()->with('supplier')->paginate(10);
    }


    public function findById(int $id): RawMaterial
    {
        return RawMaterial::with('supplier', 'product')->findOrFail($id);
    }




    public function create(Request $request): RawMaterial
    {

        $data = $this -> validateAndExtractData($request);

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
            'material_code' => 'required|string|max:255',
            'quantity' => 'required|integer',
            'unit_price' => 'required|numeric',
            'total_value' => 'required|numeric',
            'minimum_stock_level' => 'required|integer',
            'raw_material_category' => 'required|string|max:100',
            'unit_of_measurement' => 'required|string|max:100',
            'package_size' => 'nullable|string|max:100',
            'status' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'expiry_date' => 'nullable|date',
            'image.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];

        $validatedData = $request->validate($rules);

        return $validatedData;
    }

}
