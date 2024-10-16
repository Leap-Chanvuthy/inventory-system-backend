<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CurrencyAPIController extends Controller
{
    // Retrieve all currencies
    public function index()
    {
        return Currency::all();
    }

    // Store a new currency
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'base_currency_name' => 'required|string|max:50',
                'symbol' => 'required|string|max:50',
                'base_currency_value' => 'required|numeric',
                'target_currency_name' => 'required|string|max:50',
                'target_currency_value' => 'required|numeric',
                'exchage_rate' => 'required|numeric',
            ]);
    
            $currency = Currency::create($validatedData);
    
            return response()->json($currency, 201);
        }catch (ValidationException $e){
            return response() -> json(['errors' => $e -> errors()],400);
        }
    }

    // Show a specific currency
    public function show($id)
    {
        $currency = Currency::find($id);

        if (!$currency) {
            return response()->json(['message' => 'Currency not found'], 404);
        }

        return response()->json($currency);
    }

    // Update a currency
    public function update(Request $request, $id)
    {
        try {
            $currency = Currency::find($id);

            if (!$currency) {
                return response()->json(['message' => 'Currency not found'], 404);
            }
    
            $validatedData = $request->validate([
                'base_currency_name' => 'sometimes|required|string|max:50',
                'symbol' => 'sometimes|required|string|max:50',
                'base_currency_value' => 'sometimes|required|numeric',
                'target_currency_name' => 'sometimes|required|string|max:50',
                'target_currency_value' => 'sometimes|required|numeric',
                'exchage_rate' => 'sometimes|required|numeric',
            ]);
    
            $currency->update($validatedData);
    
            return response()->json($currency);
        }catch (ValidationException $e){
            return response() -> json(['errors' => $e -> errors()],400);
        }
    }

    // Delete a currency
    public function destroy($id)
    {
        $currency = Currency::find($id);

        if (!$currency) {
            return response()->json(['message' => 'Currency not found'], 404);
        }

        $currency->delete();

        return response()->json(['message' => 'Currency deleted successfully']);
    }
}
