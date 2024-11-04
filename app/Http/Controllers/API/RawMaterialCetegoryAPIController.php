<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\RawMaterialCategory;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Builder;

class RawMaterialCetegoryAPIController extends Controller
{

    // Query Builder 
    private function allBuilder(): QueryBuilder
    {
        return QueryBuilder::for(RawMaterialCategory::class)
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('catetory_name'),
                AllowedFilter::callback('search', function (Builder $query, $value) {
                    $query->where(function ($query) use ($value) {
                        $query->where('catetory_name', 'LIKE', "%{$value}%");
                    });
                })
            ])
            ->allowedSorts('created_at', 'updated_at' , 'catetory_name')
            ->defaultSort('-created_at');
    }

    public function index()
    {
        // $categories = RawMaterialCategory::all();
        $categories = $this -> allBuilder() -> paginate(10);
        return response()->json($categories);
    }

    // Store a newly created category
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'category_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $category = RawMaterialCategory::create($validatedData);

        return response()->json($category);
    }

    // Display a specific category by ID
    public function show($id)
    {
        $category = RawMaterialCategory::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found']);
        }

        return response()->json($category);
    }

    // Update a category by ID
    public function update(Request $request, $id)
    {
        $category = RawMaterialCategory::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found']);
        }

        $validatedData = $request->validate([
            'category_name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $category->update($validatedData);

        return response()->json($category);
    }

    // Remove a category by ID
    public function delete($id)
    {
        $category = RawMaterialCategory::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found']);
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }
}
