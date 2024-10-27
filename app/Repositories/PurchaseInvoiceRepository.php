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
use Illuminate\Database\Eloquent\Builder;
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
                AllowedFilter::exact('status'),  
                AllowedFilter::exact('payment_method'), 
                AllowedFilter::callback('search', function (Builder $query, $value) {
                    $query->where(function ($query) use ($value) {
                        $query->where('invoice_number', 'LIKE', "%{$value}%")
                            ->orWhere('status', 'LIKE', "%{$value}%")
                            ->orWhere('sub_total_in_usd', 'LIKE', "%{$value}%")
                            ->orWhere('discount_percentage', 'LIKE', "%{$value}%")
                            ->orWhere('tax_percentage', 'LIKE', "%{$value}%")
                            ->orWhere('grand_total_with_tax_in_usd', 'LIKE', "%{$value}%");
                    });
                }),
                AllowedFilter::callback('date_range', function (Builder $query, $value) {
                    if (isset($value['start_date']) && isset($value['end_date'])) {
                        $query->whereBetween('created_at', [$value['start_date'], $value['end_date']]);
                    }
                }),
            ])
            ->allowedSorts('created_at', 'grand_total_with_tax_in_usd', 'status')
            ->defaultSort('-created_at');
    }

    public function all(): LengthAwarePaginator
    {
        return $this->allBuilder()->with('purchaseInvoiceDetails')->paginate(10);
    }

    public function findById(int $id): PurchaseInvoice
    {
        return $this->purchaseInvoice->with('purchaseInvoiceDetails')->findOrFail($id);
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
        $subTotalInRiel = 0;
        $subTotalInUsd = 0;
        $rawMaterialsData = [];
        $supplierId = null;

        foreach ($request->raw_materials as $rawMaterialId) {
            $rawMaterial = RawMaterial::findOrFail($rawMaterialId);
            
            if (is_null($supplierId)) {
                $supplierId = $rawMaterial->supplier_id;
            }

            $totalPriceInRiel = $rawMaterial-> total_value_in_riel; 
            $totalPriceInUsd = $rawMaterial-> total_value_in_usd; 

            $rawMaterialsData[] = [
                'quantity' => $rawMaterial->quantity,
                'total_price_in_riel' => $totalPriceInRiel,
                'total_price_in_usd' => $totalPriceInUsd,
                'raw_material_id' => $rawMaterial->id,
                'supplier_id' => $supplierId,
            ];

            $subTotalInRiel += $totalPriceInRiel;
            $subTotalInUsd += $totalPriceInUsd;
        }

        $discountValueInUsd = ($request->discount_percentage / 100) * $subTotalInUsd;
        $discountValueInRiel = ($request->discount_percentage / 100) * $subTotalInRiel;

        $grandTotalWithoutTaxInUsd = $subTotalInUsd - $discountValueInUsd;
        $grandTotalWithoutTaxInRiel = $subTotalInRiel - $discountValueInRiel;

        $taxValueInUsd = ($request->tax_percentage / 100) * $grandTotalWithoutTaxInUsd;
        $taxValueInRiel = ($request->tax_percentage / 100) * $grandTotalWithoutTaxInRiel;

        $grandTotalWithTaxInUsd = $grandTotalWithoutTaxInUsd + $taxValueInUsd;
        $grandTotalWithTaxInRiel = $grandTotalWithoutTaxInRiel + $taxValueInRiel;

        $purchaseInvoice = $this->purchaseInvoice->create(array_merge($request->all(), [
            'invoice_number' => $this->generateInvoiceNumber(),
            'payment_method' => $request -> payment_method,
            'payment_date' => $request -> payment_date,
            'status' => $request -> status,
            'sub_total_in_usd' => $subTotalInUsd,
            'sub_total_in_riel' => $subTotalInRiel,
            'discount_value_in_usd' => $discountValueInUsd,
            'discount_value_in_riel' => $discountValueInRiel,
            'tax_value_in_usd' => $taxValueInUsd,
            'tax_value_in_riel' => $taxValueInRiel,
            'grand_total_without_tax_in_usd' => $grandTotalWithoutTaxInUsd,
            'grand_total_without_tax_in_riel' => $grandTotalWithoutTaxInRiel,
            'grand_total_with_tax_in_usd' => $grandTotalWithTaxInUsd,
            'grand_total_with_tax_in_riel' => $grandTotalWithTaxInRiel,
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
                'total_price_in_riel' => $totalPrice * $request->riel_conversion_rate,
                'total_price_in_usd' => $totalPrice,
                'raw_material_id' => $rawMaterial->id,
                'supplier_id' => $supplierId,
            ];

            $subTotal += $totalPrice;
        }

        $discountValue = ($request->discount_percentage / 100) * $subTotal;
        $taxValue = ($request->tax_percentage / 100) * ($subTotal - $discountValue);
        $grandTotalWithoutTax = $subTotal - $discountValue;
        $grandTotalWithTax = $grandTotalWithoutTax + $taxValue;

        $invoice->update(array_merge($request->all(), [
            'sub_total_in_usd' => $subTotal,
            'sub_total_in_riel' => $subTotal * $request->riel_conversion_rate,
            'discount_value_in_usd' => $discountValue,
            'discount_value_in_riel' => $discountValue * $request->riel_conversion_rate,
            'tax_value_in_usd' => $taxValue,
            'tax_value_in_riel' => $taxValue * $request->riel_conversion_rate,
            'grand_total_without_tax_in_usd' => $grandTotalWithoutTax,
            'grand_total_without_tax_in_riel' => $grandTotalWithoutTax * $request->riel_conversion_rate,
            'grand_total_with_tax_in_usd' => $grandTotalWithTax,
            'grand_total_with_tax_in_riel' => $grandTotalWithTax * $request->riel_conversion_rate,
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

    public function toggleStatus(int $id): PurchaseInvoice
    {
        $invoice = $this->purchaseInvoice->findOrFail($id);
        $invoice->status = !$invoice->status;
        $invoice->save();

        return $invoice;
    }
}
