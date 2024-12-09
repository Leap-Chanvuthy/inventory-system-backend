<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductAPIController extends Controller
{
    protected $productRepository;


    public function __construct( ProductRepositoryInterface $productRepository )
    {
        $this->productRepository = $productRepository;
    }

    public function index()
    {
        return $this-> productRepository->all();
    }

    public function trashed (){
        return $this -> productRepository -> trashed();
    }

    public function show ($id) {
        try {
            return $this -> productRepository -> findById($id);
        } catch (Exception $e){
            return response() -> json(["error" => $e -> getMessage()],400);
        }
    }

    public function store(Request $request)
    {
        try {
            $product = $this-> productRepository->create($request);
            return response()->json([
                'message' => 'Product created successfully.',
                'data' => $product,
            ], 201);    
        }catch (ValidationException $e){
            return response() -> json(['errors' => $e -> errors()],400);
        }        catch (\Exception $e){
            return response()->json(["error" => $e->getMessage()], 400);
        }
    }


    public function update($id , Request $request)
    {
        try {
            $product = $this-> productRepository->update($id , $request);
            return response()->json([
                'message' => 'Product updated successfully.',
                'data' => $product,
            ], 201);    
        }catch (ValidationException $e){
            return response() -> json(['errors' => $e -> errors()],400);
        }
        catch (\Exception $e){
            return response() -> json(['error' => $e -> getMessage()],500);
        }
    }


    public function destroy ($id){
        try {
            $this -> productRepository -> delete($id);
            return response() -> json([
                'message' => "Product deleted successfully"
            ]);
        }catch (Exception $e){
            return response() -> json(['error' => $e -> getMessage()],400);
        }
    }

    public function recover ($id){
        try {
            $product = Product::onlyTrashed() -> findOrFail($id);
            
            if ($product){
                $product -> restore();
                return response() -> json(['message' => 'Product restore successfully' , 'data' => $product],200);
            }

            return response() -> json(['message' => 'Product not found or already active'],400);
        }catch (\Exception $e){
            return response() -> json(['error' => $e -> getMessage()],400);
        }
    }





}
