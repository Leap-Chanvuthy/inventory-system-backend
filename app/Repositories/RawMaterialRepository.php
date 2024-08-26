<?php

namespace App\Repositories;

use App\Models\RawMaterial;
use App\Repositories\Interfaces\RawMaterialRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

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

    public function all(): LengthAwarePaginator
    {
        return $this->allBuilder()->with('supplier', 'product')->paginate(10);
    }


    public function findById(int $id): RawMaterial
    {
        return RawMaterial::with('supplier', 'product')->findOrFail($id);
    }


    // public function create(Request $request): RawMaterial
    // {
    //     $data = $this->validateAndExtractData($request);

    //     if ($request->hasFile('image')) {
    //         $file = $request->file('image');
    //         $fileName = time() . '_' . $file->getClientOriginalName();
    //         $path = $file->storeAs('raw_materials', $fileName, 'public');
    //         $data['image'] = $path;
    //     }

    //     return RawMaterial::create($data);
    // }

    public function create(Request $request): array
{
    $rawMaterialsData = $this->validateAndExtractData($request);

    $createdMaterials = [];

    foreach ($rawMaterialsData as $data) {
        if (isset($data['image'])) {
            $file = $data['image'];
            $fileName = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('raw_materials', $fileName, 'public');
            $data['image'] = $path;
        }

        $createdMaterials[] = RawMaterial::create($data);
    }

    return $createdMaterials;
}


    public function update(int $id, Request $request): RawMaterial
    {
        $data = $this->validateAndExtractData($request, $id);

        $rawMaterial = RawMaterial::findOrFail($id);

        if ($request->hasFile('image')) {
            if ($rawMaterial->image && Storage::exists($rawMaterial->image)) {
                Storage::delete($rawMaterial->image);
            }

            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $path = $image->storeAs('raw_materials', $imageName, 'public');

            $data['image'] = $path;
        }

        $rawMaterial->update($data);

        return $rawMaterial;
    }


    public function delete(int $id): void
    {
        $rawMaterial = RawMaterial::findOrFail($id);

        if ($rawMaterial->image && Storage::disk('public')->exists($rawMaterial->image)) {
            Storage::disk('public')->delete($rawMaterial->image);
        }

        $rawMaterial->delete();
    }


    // private function validateAndExtractData(Request $request, $id = null): array
    // {
    //     $rules = [
    //         'name' => 'required|string|max:50',
    //         'quantity' => 'required|integer',
    //         'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    //         'unit_price' => 'required|numeric',
    //         'total_value' => 'required|numeric',
    //         'minimum_stock_level' => 'required|integer',
    //         'unit' => 'required|string|max:100',
    //         'package_size' => 'required|string|max:100',
    //         'supplier_id' => 'required|exists:suppliers,id',
    //         'product_id' => 'nullable|exists:products,id'
    //     ];

    //     $validatedData = $request->validate($rules);

    //     return $validatedData;
    // }

    private function validateAndExtractData(Request $request, $id = null): array
{
    $rules = [
        'raw_materials' => 'required|array',
        'raw_materials.*.name' => 'required|string|max:50',
        'raw_materials.*.quantity' => 'required|integer',
        'raw_materials.*.image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        'raw_materials.*.unit_price' => 'required|numeric',
        'raw_materials.*.total_value' => 'required|numeric',
        'raw_materials.*.minimum_stock_level' => 'required|integer',
        'raw_materials.*.unit' => 'required|string|max:100',
        'raw_materials.*.package_size' => 'required|string|max:100',
        'raw_materials.*.supplier_id' => 'required|exists:suppliers,id',
        'raw_materials.*.product_id' => 'nullable|exists:products,id'
    ];

    $validatedData = $request->validate($rules);

    return $validatedData['raw_materials'];
}

}
