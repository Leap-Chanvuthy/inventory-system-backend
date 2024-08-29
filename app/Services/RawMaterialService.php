<?php

namespace App\Services;

use App\Models\RawMaterial;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceDetail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RawMaterialService
{

    private function generateInvoiceNumber(): string
    {
        $lastInvoice = PurchaseInvoice::orderBy('created_at', 'desc')->first();

        if ($lastInvoice && preg_match('/INV-\d{8}-(\d{6})/', $lastInvoice->invoice_number, $matches)) {
            $lastNumber = intval($matches[1]);
        } else {
            $lastNumber = 0;
        }

        $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);

        $datePart = Carbon::now()->format('Ymd');

        return 'INV-' . $datePart . '-' . $newNumber;
    }


    public function createRawMaterialsWithInvoice(
        array $rawMaterialsData,
        float $discountPercentage = 0,
        float $discountValue = 0,
        float $taxPercentage = 0,
        float $taxValue = 0,
        string $paymentMethod = 'Default', 
        string $status = 'Pending',         
        float $indebted = 0,               
        float $clearingPayable = 0         
    ): array {
        DB::beginTransaction();
    
        try {
            $rawMaterials = [];
            $invoiceDetails = [];
    
            $totalAmount = 0;
            $supplierId = null;
    
            foreach ($rawMaterialsData as $data) {
                $rawMaterial = RawMaterial::create($data);
                $rawMaterials[] = $rawMaterial;
    
                $totalAmount += $rawMaterial->total_value;
    
                if ($supplierId === null) {
                    $supplierId = $rawMaterial->supplier_id;
                }
    
                $invoiceDetail = [
                    'quantity' => $rawMaterial->quantity,
                    'total_price' => $rawMaterial->total_value,
                    'raw_material_id' => $rawMaterial->id,
                ];
                $invoiceDetails[] = $invoiceDetail;
            }
    
            $discountAmount = ($totalAmount * $discountPercentage / 100) + $discountValue;
            $subTotal = $totalAmount - $discountAmount;
            $taxAmount = ($subTotal * $taxPercentage / 100) + $taxValue;
            $grandTotal = $subTotal + $taxAmount;
    
            $invoiceNumber = $this->generateInvoiceNumber();
    
            $invoice = PurchaseInvoice::create([
                'total_amount' => $grandTotal,
                'payment_method' => $paymentMethod, 
                'invoice_number' => $invoiceNumber,
                'payment_date' => now(),
                'supplier_id' => $supplierId,
                'status' => $status,                
                'sub_total' => $subTotal,
                'grand_total' => $grandTotal,
                'clearing_payable' => $clearingPayable, 
                'indebted' => $indebted,             
                'discount_percentage' => $discountPercentage,
                'discount_value' => $discountAmount,
                'tax_percentage' => $taxPercentage,
                'tax_value' => $taxAmount,
            ]);
    
            foreach ($invoiceDetails as $detail) {
                $detail['purchase_invoice_id'] = $invoice->id;
                PurchaseInvoiceDetail::create($detail);
            }
    
            DB::commit();
    
            return [
                'raw_materials' => $rawMaterials,
                'invoice' => $invoice,
                'invoice_details' => $invoiceDetails
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    

    public function updateRawMaterialsWithInvoice(
        int $invoiceId,
        array $rawMaterialsData,
        float $discountPercentage = 0,
        float $discountValue = 0,
        float $taxPercentage = 0,
        float $taxValue = 0,
        string $paymentMethod = 'Default', 
        string $status = 'Pending',         
        float $indebted = 0,               
        float $clearingPayable = 0         
    ): array {
        DB::beginTransaction();
    
        try {
            $invoice = PurchaseInvoice::findOrFail($invoiceId);
            $existingDetails = $invoice->purchaseInvoiceDetails()->get()->keyBy('raw_material_id');
    
            $rawMaterials = [];
            $invoiceDetails = [];
            $totalAmount = 0;
            $supplierId = null;
    
            $currentRawMaterialIds = [];
    
            foreach ($rawMaterialsData as $data) {
                $rawMaterialId = $data['id'] ?? null;
    
                if ($rawMaterialId) {
                    $rawMaterial = RawMaterial::withTrashed()->findOrFail($rawMaterialId);
                    $rawMaterial->update($data);
                } else {
                    $rawMaterial = RawMaterial::create($data);
                }

                $currentRawMaterialIds[] = $rawMaterial->id;
    
                $rawMaterials[] = $rawMaterial;
                $totalAmount += $rawMaterial->total_value;
    
                if ($supplierId === null) {
                    $supplierId = $rawMaterial->supplier_id;
                }
    
                if (isset($existingDetails[$rawMaterial->id])) {
                    $invoiceDetail = $existingDetails[$rawMaterial->id];
                    $invoiceDetail->update([
                        'quantity' => $rawMaterial->quantity,
                        'total_price' => $rawMaterial->total_value
                    ]);
                } else {
                    $invoiceDetail = [
                        'quantity' => $rawMaterial->quantity,
                        'total_price' => $rawMaterial->total_value,
                        'raw_material_id' => $rawMaterial->id,
                        'purchase_invoice_id' => $invoice->id
                    ];
                    PurchaseInvoiceDetail::create($invoiceDetail);
                }
    
                $invoiceDetails[] = $invoiceDetail;
            }
    
            // Delete removed raw materials
            $deletedDetails = $existingDetails->whereNotIn('raw_material_id', $currentRawMaterialIds);
            foreach ($deletedDetails as $detail) {
                $detail->delete();
                RawMaterial::withTrashed()->findOrFail($detail->raw_material_id)->forceDelete();
            }
    
            $discountAmount = ($totalAmount * $discountPercentage / 100) + $discountValue;
            $subTotal = $totalAmount - $discountAmount;
            $taxAmount = ($subTotal * $taxPercentage / 100) + $taxValue;
            $grandTotal = $subTotal + $taxAmount;
    
            $invoice->update([
                'total_amount' => $grandTotal,
                'payment_method' => $paymentMethod, 
                'sub_total' => $subTotal,
                'grand_total' => $grandTotal,
                'discount_percentage' => $discountPercentage,
                'discount_value' => $discountAmount,
                'tax_percentage' => $taxPercentage,
                'tax_value' => $taxAmount,
                'status' => $status,                
                'clearing_payable' => $clearingPayable, 
                'indebted' => $indebted             
            ]);
    
            DB::commit();
    
            return [
                'raw_materials' => $rawMaterials,
                'invoice' => $invoice,
                'invoice_details' => $invoiceDetails
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
       
}

