<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceDetail;
use App\Models\RawMaterial;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PurchaseInvoiceAPIController extends Controller
{

public function store(Request $request)
{
    try {
        $validatedData = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'payment_method' => 'required|string',
            'invoice_number' => 'required|string|unique:purchase_invoices,invoice_number',
            'payment_date' => 'nullable|date',
            'discount_percentage' => 'nullable|numeric',
            'tax_percentage' => 'nullable|numeric',
            'status' => 'required|string',
            'clearing_payable' => 'nullable|numeric',
            'indebted' => 'nullable|numeric',
            'raw_materials' => 'required|array',
            'raw_materials.*' => 'required|exists:raw_materials,id',
        ]);

        $supplierId = $request->supplier_id;

        $subTotal = 0;
        $rawMaterialsData = [];

        foreach ($request->raw_materials as $rawMaterialId) {
            $rawMaterial = RawMaterial::findOrFail($rawMaterialId);

            if ($rawMaterial->supplier_id !== $supplierId) {
                throw new \Exception("Raw material with ID {$rawMaterialId} does not belong to the selected supplier with ID {$supplierId}.");
            }

            $totalPrice = $rawMaterial->quantity * $rawMaterial->unit_price;

            $rawMaterialsData[] = [
                'quantity' => $rawMaterial->quantity,  
                'total_price' => $totalPrice,
                'raw_material_id' => $rawMaterial->id,
            ];

            $subTotal += $totalPrice;
        }

        $discountValue = ($request->discount_percentage / 100) * $subTotal;
        $taxValue = ($request->tax_percentage / 100) * ($subTotal - $discountValue);
        $grandTotalWithoutTax = $subTotal - $discountValue;
        $grandTotalWithTax = $grandTotalWithoutTax + $taxValue;

        $purchaseInvoice = PurchaseInvoice::create(array_merge($validatedData, [
            'sub_total' => $subTotal,
            'discount_value' => $discountValue,
            'tax_value' => $taxValue,
            'grand_total_without_tax' => $grandTotalWithoutTax,
            'grand_total_with_tax' => $grandTotalWithTax,
        ]));

        foreach ($rawMaterialsData as $material) {
            $material['purchase_invoice_id'] = $purchaseInvoice->id; 
            PurchaseInvoiceDetail::create($material);
        }

        return response()->json($purchaseInvoice, 201);

    } catch (ValidationException $e){
        return response() -> json(['errors' => $e -> errors()],400);
    } 
    catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 400);
    }
}




}
