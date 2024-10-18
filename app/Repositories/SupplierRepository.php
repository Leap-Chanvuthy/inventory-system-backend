<?php

namespace App\Repositories;

use App\Models\RawMaterial;
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
                AllowedFilter::exact('supplier_status'),
                AllowedFilter::exact('supplier_category'),
                AllowedFilter::callback('search', function (Builder $query, $value) {
                    $query->where(function ($query) use ($value) {
                        $query->where('location', 'LIKE', "%{$value}%")
                            ->orWhere('name', 'LIKE', "%{$value}%")
                            ->orWhere('phone_number', 'LIKE', "%{$value}%")
                            ->orWhere('location', 'LIKE', "%{$value}%")
                            ->orWhere('address', 'LIKE', "%{$value}%")
                            ->orWhere('contact_person', 'LIKE', "%{$value}%")
                            ->orWhere('business_registration_number', 'LIKE', "%{$value}%")
                            ->orWhere('bank_name', 'LIKE', "%{$value}%");
                    });
                }),

            ])
            ->allowedSorts('created_at', 'updated_at')
            ->defaultSort('-created_at');
    }

    private function validateAndExtractData(Request $request, $id = null)
    {
        $rule = [
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'name' => 'required|string|max:255',
            'supplier_code' => 'string|max:100|unique:suppliers,supplier_code',
            'phone_number' => 'required|string|max:50',
            'location' => 'required|string|max:255',
            'longitude' => 'nullable|string|max:100',
            'latitude' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:suppliers,email',
            'contact_person' => 'required|string|max:255',
            'website' => 'nullable|string|max:255',
            'social_media' => 'nullable|string|max:255',
            'supplier_category' => 'nullable|string|max:255',
            'supplier_status' => 'nullable|string|max:100',
            'contract_length' => 'nullable|string|max:100',
            'discount_term' => 'nullable|string|max:100',
            'payment_term' => 'nullable|string|max:100',
            'business_registration_number' => 'nullable|string|max:100',
            'vat_number' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_account_name' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:255',
            'note' => 'nullable|string',
        ];

        $validatedData = $request->validate($rule);
        return $validatedData;
    }


    private function validateChange(Request $request, $id)
    {

        $rule = [
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'name' => 'required|string|max:255',
            'supplier_code' => 'nullable|string|max:100|unique:suppliers,supplier_code' . ($id ? ",$id" : ''),
            'phone_number' => 'required|string|max:50',
            'location' => 'required|string|max:255',
            'longitude' => 'nullable|string|max:100',
            'latitude' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('suppliers')->ignore($id),
            ],
            'contact_person' => 'required|string|max:255',
            'website' => 'nullable|string|max:255',
            'social_media' => 'nullable|string|max:255',
            'supplier_category' => 'nullable|string|max:255',
            'supplier_status' => 'nullable|string|max:100',
            'contract_length' => 'nullable|string|max:100',
            'discount_term' => 'nullable|string|max:100',
            'payment_term' => 'nullable|string|max:100',
            'business_registration_number' => 'nullable|string|max:100',
            'vat_number' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_account_name' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:255',
            'note' => 'nullable|string',
        ];

        $validatedData = $request->validate($rule);
        return $validatedData;
    }

    private function generateSupplierCode(): string
    {
        $lastSupplier = Supplier::withTrashed() -> orderBy('created_at', 'desc')->first(); // change to withTrashed()
        if ($lastSupplier && preg_match('/SUPP-(\d{6})/', $lastSupplier->supplier_code, $matches)) {
            $lastCode = intval($matches[1]);
        } else {
            $lastCode = 0;
        }
        $newNumber = str_pad($lastCode + 1, 6, '0', STR_PAD_LEFT);
        return 'SUPP-' . $newNumber;
    }



    public function all(): LengthAwarePaginator
    {
        return $this->allBuilder()->with('raw_materials')->paginate(10);
    }

    public function findById(int $id): Supplier
    {
        return Supplier::with('raw_materials')->findOrFail($id);
    }

    public function create(Request $request): Supplier
    {
        $supplier = $this->validateAndExtractData($request);
        $supplier['supplier_code'] = $this->generateSupplierCode();
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
        $supplierData = $this->validateChange($request, $id);
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
        } else {
            $supplierData['image'] = $supplier->image;
        }

        $supplier->update($supplierData);

        // relationship manager
        if ($request->has('raw_materials')) {
            $rawMaterialIds = $request->input('raw_materials');

            $supplier->raw_materials()->whereNotIn('id', $rawMaterialIds)->update(['supplier_id' => null]);

            foreach ($rawMaterialIds as $rawMaterialId) {
                $rawMaterial = RawMaterial::find($rawMaterialId);

                if ($rawMaterial) {

                    $rawMaterial->supplier_id = $supplier->id;
                    $rawMaterial->save();
                }
            }
        }
        return $supplier;
    }


    public function delete(int $id): void
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();
    }
}
