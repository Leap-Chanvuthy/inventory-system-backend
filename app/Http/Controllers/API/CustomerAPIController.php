<?php

namespace App\Http\Controllers\API;

use App\Exports\CustomerExport;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use Exception;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class CustomerAPIController extends Controller
{
    private function allBuilder(): QueryBuilder
    {
        return QueryBuilder::for(Customer::class)
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('customer_category'),
                AllowedFilter::exact('customer_status'),
                AllowedFilter::callback('search', function (Builder $query, $value) {
                    $query->where(function ($query) use ($value) {
                        // $query->where('customer_category', 'LIKE', "%{$value}%");
                        // $query->orWhere('customer_status', 'LIKE', "%{$value}%"); 
                        $query->where('fullname', 'LIKE', "%{$value}%");
                        $query->orWhere('email_address', 'LIKE', "%{$value}%");  
                        $query->orWhere('shipping_address', 'LIKE', "%{$value}%");
                        $query->orWhere('phone_number', 'LIKE', "%{$value}%");
                        $query->orWhere('social_media', 'LIKE', "%{$value}%");                         
                    });
                })
            ])
            ->allowedSorts('created_at', 'updated_at')
            ->defaultSort('-created_at');
    }


    private function allBuilderWithTrashed(): QueryBuilder
    {
        return QueryBuilder::for(Customer::class)
            ->onlyTrashed()
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('customer_category'),
                AllowedFilter::exact('customer_status'),
                AllowedFilter::callback('search', function (Builder $query, $value) {
                    $query->where(function ($query) use ($value) {
                        $query->where('customer_category', 'LIKE', "%{$value}%");
                        $query->where('customer_status', 'LIKE', "%{$value}%"); 
                        $query->orWhere('fullname', 'LIKE', "%{$value}%");
                        $query->orWhere('email_address', 'LIKE', "%{$value}%");  
                        $query->orWhere('shipping_address', 'LIKE', "%{$value}%");
                        $query->orWhere('phone_number', 'LIKE', "%{$value}%");
                        $query->orWhere('social_media', 'LIKE', "%{$value}%");                         
                    });
                })
            ])
            ->allowedSorts('created_at', 'updated_at')
            ->defaultSort('-created_at');
    }


    private function validateAndExtractData(Request $request, $id = null): array
    {
        $rules = [
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:10000',
            'fullname' => 'required|string|max:50',
            'email_address' => 'required|email|max:50',
            'phone_number' => 'required|string|max:50',
            'social_media' => 'nullable|string|max:100',
            'shipping_address' => 'required|string|max:255',
            'longitude' => 'nullable|string|max:100',
            'latitude' => 'nullable|string|max:100',
            'customer_status' => 'required|string|max:255',
            'customer_category_id' => 'required|exists:customer_categories,id',
            'customer_note' => 'nullable|string',
        ];

        $validatedData = $request->validate($rules);

        return $validatedData;
    }


    public function index (){
        try {
            $product = $this -> allBuilder() -> with('category') -> paginate(10);
            return response() -> json($product);
        }catch (Exception $e){
            return response() -> json(['error' => $e -> getMessage()],400);
        }
    }


    public function trashed (){
        try {
            $product = $this -> allBuilderWithTrashed() -> with('category') -> paginate(10);
            return response() -> json($product);
        }catch (Exception $e){
            return response() -> json(['error' => $e -> getMessage()],400);
        }
    }

    public function show ($id){
        try {
            $product = Customer::with('category') -> findOrFail($id);
            return response() -> json($product);
        }catch (Exception $e){
            return response() -> json(['error' => $e -> getMessage()],400);
        }
    }



    public function store (Request $request){
        try {
            $data = $this -> validateAndExtractData($request);
            if ($request -> hasFile('image')){
                $file = $request-> file('image');
                $fileName = time()."_" . $file-> getClientOriginalName();
                $path = $file -> storeAs('customers' , $fileName , 'public');
                $data['image'] = $path;
            }
            Customer::create($data);
            return response() -> json(['message' => 'Customer created successfullly'],201);

        }catch (ValidationException $e){
            return response() -> json(['errors' => $e -> errors()],400);
        }catch (Exception $e){
            return response() -> json(['error' => $e -> getMessage()],400);
        }
    }



    public function update(Request $request, $id)
    {
        try {
            $data = $this->validateAndExtractData($request, $id);
            $customer = Customer::findOrFail($id);

            if ($request->hasFile('image')) {
                if ($customer->image && Storage::disk('public')->exists($customer->image)) {
                    Storage::disk('public')->delete($customer->image);
                }
    
                $file = $request->file('image');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('customers', $fileName, 'public');
                $data['image'] = $path;
            } else {
                $data['image'] = $customer->image;
            }

            $customer->update($data);

            return response()->json(['message' => 'Customer updated successfully'], 200);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 400);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function recover ($id){
        try {
            $customer = Customer::onlyTrashed() -> findOrFail($id);
            
            if ($customer){
                $customer -> restore();
                return response() -> json(['message' => 'Customer restore successfully' , 'data' => $customer],200);
            }

            return response() -> json(['message' => 'Customer not found or already active'],400);
        }catch (\Exception $e){
            return response() -> json(['error' => $e -> getMessage()],400);
        }
    }


    public function destroy ($id){
        $customer = Customer::findOrFail($id);
        $customer -> delete();
    }



    public function export(Request $request)
    {
        try {
            $filters = $request->all();

            return Excel::download(new CustomerExport($request), 'customers.xlsx');
        }  catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            return response()->json(['errors' => $e->failures()], 422); 
        }  catch (\Exception $e) {
            return response()->json(['error' => 'Import failed: ' . $e->getMessage()], 500);
        }
    }


    


}
