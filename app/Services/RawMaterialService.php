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
        float $taxValue = 0
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
                'payment_method' => 'Default',
                'invoice_number' => $invoiceNumber,
                'payment_date' => now(),
                'supplier_id' => $supplierId,
                'status' => 'Pending',
                'sub_total' => $subTotal,
                'grand_total' => $grandTotal,
                'clearing_payable' => 0, 
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
                'message' => 'Raw material and invoice created successfully',
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

