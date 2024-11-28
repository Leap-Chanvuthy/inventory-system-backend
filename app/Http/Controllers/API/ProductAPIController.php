<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\ProductRepositoryInterface;
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


}
