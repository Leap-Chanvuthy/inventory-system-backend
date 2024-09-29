<?php

namespace App\Repositories;

use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceDetail;
use App\Models\RawMaterial;
use App\Repositories\Interfaces\PurchaseInvoiceRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Exception;

class PurchaseInvoiceRepository implements PurchaseInvoiceRepositoryInterface
{
    protected $purchaseInvoice;

    public function __construct(PurchaseInvoice $purchaseInvoice)
    {
        $this->purchaseInvoice = $purchaseInvoice;
    }

    private function allBuilder(): QueryBuilder
    {
        return QueryBuilder::for(PurchaseInvoice::class)
            ->allowedIncludes(['purchaseInvoiceDetails'])
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('invoice_number'),
                AllowedFilter::exact('payment_method'),
                AllowedFilter::exact('status'),
                AllowedFilter::partial('total_amount'),
                AllowedFilter::partial('discount_percentage'),
                AllowedFilter::partial('tax_percentage'),
                AllowedFilter::partial('sub_total'),
                AllowedFilter::partial('grand_total'),
            ])
            ->allowedSorts('created_at', 'total_amount', 'status')
            ->defaultSort('-created_at');
    }

    public function all(): LengthAwarePaginator
    {
        return $this->allBuilder()->with('purchaseInvoiceDetails')->paginate(10);
    }

    public function findById(int $id): PurchaseInvoice
    {
        return $this->purchaseInvoice->with('details')->findOrFail($id);
    }

    public function generateInvoiceNumber(): string
    {
        $lastInvoice = PurchaseInvoice::orderBy('created_at', 'desc')->first();

        if ($lastInvoice && preg_match('/INV-(\d{6})/', $lastInvoice->invoice_number, $matches)) {
            $lastCode = intval($matches[1]);
        } else {
            $lastCode = 0; 
        }

        $newNumber = str_pad($lastCode + 1, 6, '0', STR_PAD_LEFT);
        return 'INV-' . $newNumber;
    }

    public function create(Request $request): PurchaseInvoice
    {
        $supplierId = $request->supplier_id;

        $subTotal = 0;
        $rawMaterialsData = [];

        foreach ($request->raw_materials as $rawMaterialId) {
            $rawMaterial = RawMaterial::findOrFail($rawMaterialId);

            if ($rawMaterial->supplier_id !== $supplierId) {
                throw new Exception("Raw material with ID {$rawMaterialId} does not belong to the selected supplier with ID {$supplierId}.");
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

        $purchaseInvoice = $this->purchaseInvoice->create(array_merge($request->all(), [
            'invoice_number' => $this->generateInvoiceNumber(),
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

        return $purchaseInvoice;
    }

    public function update(int $id, Request $request): PurchaseInvoice
    {
        $invoice = $this->purchaseInvoice->findOrFail($id);
        $supplierId = $request->supplier_id;

        $subTotal = 0;
        $rawMaterialsData = [];

        foreach ($request->raw_materials as $rawMaterialId) {
            $rawMaterial = RawMaterial::findOrFail($rawMaterialId);

            if ($rawMaterial->supplier_id !== $supplierId) {
                throw new Exception("Raw material with ID {$rawMaterialId} does not belong to the selected supplier with ID {$supplierId}.");
            }

            $totalPrice = $rawMaterial->quantity * $rawMaterial->unit_price;

            $rawMaterialsData[$rawMaterialId] = [
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

        $invoice->update(array_merge($request->all(), [
            'sub_total' => $subTotal,
            'discount_value' => $discountValue,
            'tax_value' => $taxValue,
            'grand_total_without_tax' => $grandTotalWithoutTax,
            'grand_total_with_tax' => $grandTotalWithTax,
        ]));

        $existingDetails = $invoice->purchaseInvoiceDetails->keyBy('raw_material_id');

        foreach ($rawMaterialsData as $rawMaterialId => $materialData) {
            if ($existingDetails->has($rawMaterialId)) {
                $existingDetail = $existingDetails[$rawMaterialId];
                $existingDetail->update($materialData);
            } else {
                $materialData['purchase_invoice_id'] = $invoice->id;
                PurchaseInvoiceDetail::create($materialData);
            }
        }

        $newRawMaterialIds = collect($request->raw_materials)->toArray();
        $existingDetails->filter(function ($detail) use ($newRawMaterialIds) {
            return !in_array($detail->raw_material_id, $newRawMaterialIds);
        })->each->delete();

        return $invoice;
    }

    public function delete(int $id): void
    {
        $invoice = $this->purchaseInvoice->findOrFail($id);
        foreach ($invoice->purchaseInvoiceDetails as $detail) {
            $detail->delete();
        }
        $invoice->delete();
    }

    public function restore(int $id): PurchaseInvoice
    {
        $invoice = $this->purchaseInvoice->withTrashed()->findOrFail($id);
        $invoice->restore();

        foreach ($invoice->purchaseInvoiceDetails()->withTrashed()->get() as $detail) {
            $detail->restore();
        }

        return $invoice;
    }
}
