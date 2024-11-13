<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\PurchaseInvoiceRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PurchaseInvoiceAPIController extends Controller
{
    protected $purchaseInvoiceRepository;

    public function __construct(PurchaseInvoiceRepositoryInterface $purchaseInvoiceRepository)
    {
        $this->purchaseInvoiceRepository = $purchaseInvoiceRepository;
    }

    public function index()
    {
        try {
            return $this->purchaseInvoiceRepository->all();
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function show($id)
    {
        try {
            $purchase_invoice = $this->purchaseInvoiceRepository->findById($id);
            return response()->json($purchase_invoice, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function store(Request $request)
    {
        try {

            $request->validate([
                'raw_materials' => 'required|array', 
                'raw_materials*' => 'required|exists:raw_materials,id',
                'discount_percentage' => 'required|numeric|numeric|min:0|max:100',
                'tax_percentage' => 'required|numeric|numeric|min:0|max:100',
                'payment_method' => "required|string",
                // 'status' => 'required|string',
                'payment_date' => 'required|date',
                'clearing_payable_percentage' => 'required|numeric|min:0|max:100',
            ]);
    
            $invoice = $this->purchaseInvoiceRepository->create($request);
    
            return response()->json(['message' => 'Invoice created successfully' , 'invoice' => $invoice], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    

    // public function update($id, Request $request)
    // {
    //     try {
    //         $validatedData = $request->validate([
    //             'supplier_id' => 'required|exists:suppliers,id',
    //             'payment_method' => 'required|string',
    //             'invoice_number' => 'required|string|unique:purchase_invoices,invoice_number,' . $id,
    //             'payment_date' => 'nullable|date',
    //             'discount_percentage' => 'nullable|numeric',
    //             'tax_percentage' => 'nullable|numeric',
    //             'status' => 'required|string',
    //             'clearing_payable' => 'nullable|numeric',
    //             'indebted' => 'nullable|numeric',
    //             'riel_conversion_rate' => 'required|numeric',
    //             'usd_amount' => 'nullable|numeric',
    //             'riel_amount' => 'nullable|numeric',
    //             'raw_materials' => 'required|array',
    //             'raw_materials.*' => 'required|exists:raw_materials,id',
    //         ]);

    //         $invoice = $this->purchaseInvoiceRepository->update($id, $request);
    //         return response()->json(['invoice' => $invoice], 200);
    //     } catch (ValidationException $e) {
    //         return response()->json(['errors' => $e->errors()], 422);
    //     }
    // }


    public function update(int $id, Request $request)
    {
        try {
            // Validate the incoming request
            $request->validate([
                'raw_materials' => 'required|array',
                'raw_materials.*' => 'required|exists:raw_materials,id',
                'discount_percentage' => 'required|numeric|numeric|min:0|max:100',
                'tax_percentage' => 'required|numeric|numeric|min:0|max:100',
                'payment_method' => 'required|string',
                // 'status' => 'required|string',
                'payment_date' => 'required|date',
                'clearing_payable_percentage' => 'required|numeric|min:0|max:100',
            ]);
    
            // Update the purchase invoice
            $invoice = $this->purchaseInvoiceRepository->update($id, $request);
    
            // Return a successful response with the updated invoice
            return response()->json(['message' => 'Invoice updated successfully', 'invoice' => $invoice], 200);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    

    public function destroy($id)
    {
        try {
            $this->purchaseInvoiceRepository->delete($id);
            return response()->json(['message' => 'Purchase invoice deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function restore($id)
    {
        try {
            $invoice = $this->purchaseInvoiceRepository->restore($id);
            return response()->json(['message' => 'Purchase invoice and its details successfully restored', 'invoice' => $invoice], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
