<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CustomerCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Builder;

class CustomerCategoryAPIController extends Controller
{
    private function allBuilder(): QueryBuilder
    {
        return QueryBuilder::for(CustomerCategory::class)
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('category_name'),
                AllowedFilter::callback('search', function (Builder $query, $value) {
                    $query->where(function ($query) use ($value) {
                        $query->where('category_name', 'LIKE', "%{$value}%");
                    });
                })
            ])
            ->allowedSorts('created_at', 'updated_at' , 'catetory_name')
            ->defaultSort('-created_at');
    }

    public function index()
    {
        $categories = $this -> allBuilder() -> paginate(10);
        return response()->json($categories);
    }

    public function getWithoutPagination()
    {
        $categories = CustomerCategory::all();
        return response()->json($categories);
    }

    // Store a newly created category
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'category_name' => 'required|uppercase|string|max:255',
                'description' => 'required|string|max:500',
            ]);
    
            $category = CustomerCategory::create($validatedData);
    
            return response()->json($category);
        }catch (ValidationException $e){
            return response() -> json(['errors' => $e -> errors()],400);
        }
    }

    // Display a specific category by ID
    public function show($id)
    {
        $category = CustomerCategory::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found']);
        }

        return response()->json($category);
    }

    // Update a category by ID
    public function update(Request $request, $id)
    {
        try {
            $category = CustomerCategory::find($id);

            if (!$category) {
                return response()->json(['message' => 'Category not found']);
            }
    
            $validatedData = $request->validate([
                'category_name' => 'required|uppercase|string|max:255',
                'description' => 'required|string|max:500',
            ]);
    
            $category->update($validatedData);
    
            return response()->json($category);
        }catch (ValidationException $e){
            return response() -> json(['errors' => $e -> errors()],400);
        }
    }


    public function delete($id)
    {
        $category = CustomerCategory::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found']);
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }
}
