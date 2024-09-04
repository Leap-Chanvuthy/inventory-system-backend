<?php
namespace App\Repositories;

use App\Models\Supplier;
use App\Repositories\Interfaces\SupplierRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Builder;

class SupplierRepository implements SupplierRepositoryInterface
{
    protected $supplier;

    public function __construct(Supplier $supplier)
    {
        $this->supplier = $supplier;
    }

    /**
     * Build query with filters and includes
     * @return QueryBuilder
     */
    private function allBuilder(): QueryBuilder
    {
        return QueryBuilder::for(Supplier::class)
            ->allowedIncludes(['products'])
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::callback('search', function (Builder $query, $value) {
                    $query->where(function ($query) use ($value) {
                        $query->where('location', 'LIKE', "%{$value}%")
                              ->orWhere('name', 'LIKE', "%{$value}%")
                              ->orWhere('phone_number', 'LIKE', "%{$value}%")
                              ->orWhere('location', 'LIKE', "%{$value}%")
                              ->orWhere('address', 'LIKE', "%{$value}%")
                              ->orWhere('city', 'LIKE', "%{$value}%")
                              ->orWhere('contact_person', 'LIKE', "%{$value}%")
                              ->orWhere('business_registration_number' , 'LIKE' , "%{$value}%")
                              ->orWhere('bank_name' , 'LIKE' , "%{$value}%");
                    });
                }),
                
            ])
            ->allowedSorts('created_at', 'updated_at', 'name', 'location')
            ->defaultSort('-created_at');
    }

    private function validateAndExtractData(Request $request, $id = null){
        $rule = [
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:50',
            'location' => 'required|string|max:255',
            'longitude' => 'nullable|string|max:100',
            'latitude' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'email' => 'required|string|email|max:255|unique:suppliers,email',
            'contact_person' => 'required|string|max:255',
            'business_registration_number' => 'nullable|string|max:100',
            'vat_number' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_account_name' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:255',
            'note' => 'required|string',
        ];

        $validatedData = $request -> validate($rule);
        return $validatedData;
    }

    private function validateChange (Request $request , $id){
        $rule = [
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:50',
            'location' => 'required|string|max:255',
            'longitude' => 'nullable|string|max:100',
            'latitude' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('suppliers')->ignore($id),
            ],
            'contact_person' => 'required|string|max:255',
            'business_registration_number' => 'nullable|string|max:100',
            'vat_number' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_account_name' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:255',
            'note' => 'required|string',
        ];
        $validatedData = $request -> validate($rule);
        return $validatedData;
    }


    public function all(): LengthAwarePaginator
    {
        return $this->allBuilder() ->with('products') ->paginate(10);
    }

    public function findById(int $id): Supplier
    {
        return Supplier::with('products')->findOrFail($id);
    }

    public function create(Request $request): Supplier
    {
        $supplier = $this -> validateAndExtractData($request);
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('suppliers', $fileName, 'public');
            $supplier['image'] = $path;
        }

        return Supplier::create($supplier);
    }

    public function update(Request $request, $id): Supplier
    {
       $supplierData = $this -> validateChange($request , $id);
        // $supplierData = $request -> all();
        $supplier = Supplier::findOrFail($id);
    
        if ($request->hasFile('image')) {
            if ($supplier->image && Storage::disk('public')->exists($supplier->image)) {
                Storage::disk('public')->delete($supplier->image);
            }

            $file = $request->file('image');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('suppliers', $fileName, 'public');
            $supplierData['image'] = $path;
        }
        $supplier->update($supplierData);
    
        return $supplier;
    }

    public function delete(int $id): void
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();
    }
}
