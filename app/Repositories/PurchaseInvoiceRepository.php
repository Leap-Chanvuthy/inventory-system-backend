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
use Carbon\Carbon;


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
                        $startDate = Carbon::parse($value['start_date'])->startOfDay();
                        $endDate = Carbon::parse($value['end_date'])->endOfDay();
                        $query->whereBetween('created_at', [$startDate, $endDate]);
                    }
                }),               
            ])
            ->allowedSorts('created_at', 'grand_total_with_tax_in_usd', 'status')
            ->defaultSort('-created_at');
    }

    private function allBuilderWithTrashed (): QueryBuilder
    {
        return QueryBuilder::for(PurchaseInvoice::class)
            ->onlyTrashed() 
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
                        $startDate = Carbon::parse($value['start_date'])->startOfDay();
                        $endDate = Carbon::parse($value['end_date'])->endOfDay();
                        $query->whereBetween('created_at', [$startDate, $endDate]);
                    }
                }),               
            ])
            ->allowedSorts('created_at', 'grand_total_with_tax_in_usd', 'status')
            ->defaultSort('-created_at');
    }

    public function all(): LengthAwarePaginator
    {
        return $this->allBuilder()->with(['purchaseInvoiceDetails.rawMaterial' => function ($query) {$query->withTrashed();}, 'purchaseInvoiceDetails.rawMaterial' , 'supplier' ])->paginate(10);
    }

    public function trashed(): LengthAwarePaginator
    {
        return $this->allBuilderWithTrashed()->with('purchaseInvoiceDetails.rawMaterial' , 'supplier')->paginate(10);
    }


    public function findById(int $id): PurchaseInvoice
    {
        return $this->purchaseInvoice-> with(['purchaseInvoiceDetails.rawMaterial' , 'purchaseInvoiceDetails.rawMaterial' => function ($query) {$query->withTrashed();} , 'purchaseInvoiceDetails.rawMaterial.category' , 'supplier'])->withTrashed()->findOrFail($id);
    }

    public function generateInvoiceNumber(): string
    {
        $lastInvoice = PurchaseInvoice::withTrashed()
            ->selectRaw('MAX(CAST(SUBSTRING(invoice_number, 5) AS UNSIGNED)) AS max_code')
            ->first();

        $lastCode = $lastInvoice->max_code ?? 0;

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
            $rawMaterial = RawMaterial::withTrashed() ->findOrFail($rawMaterialId);

            if (is_null($rawMaterial->supplier_id)) {
                throw new \Exception("Raw material ID {$rawMaterial->id} does not belong to any supplier. All raw materials must belong to a supplier to create invoice.");
            }
            
            if (is_null($supplierId)) {
                $supplierId = $rawMaterial->supplier_id;
            }elseif ($rawMaterial->supplier_id !== $supplierId) {
                throw new \Exception("Raw material ID {$rawMaterial->id} belongs to a different supplier. All raw materials must belong to the same supplier.");
            }

            $totalPriceInRiel = $rawMaterial->total_value_in_riel; 
            $totalPriceInUsd = $rawMaterial->total_value_in_usd; 

            $rawMaterialsData[] = [
                'quantity' => $rawMaterial->quantity,
                'total_price_in_riel' => $totalPriceInRiel,
                'total_price_in_usd' => $totalPriceInUsd,
                'raw_material_id' => $rawMaterial->id,
                // 'supplier_id' => $supplierId,
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

        $clearingPayablePercentage = $request->clearing_payable_percentage;

        $clearingPayableInUsd = ($clearingPayablePercentage / 100) * $grandTotalWithTaxInUsd;
        $clearingPayableInRiel = ($clearingPayablePercentage / 100) * $grandTotalWithTaxInRiel;

        $indebtedInUsd = max(0, $grandTotalWithTaxInUsd - $clearingPayableInUsd);
        $indebtedInRiel = max(0, $grandTotalWithTaxInRiel - $clearingPayableInRiel);

        $status = 'PAID';
        if ($clearingPayablePercentage == 0) {
            $status = 'UNPAID';
        } elseif ($clearingPayablePercentage < 100) {
            $status = 'INDEBTED';
        } elseif ($clearingPayablePercentage > 100) {
            $status = 'OVERPAID';
        }

        $purchaseInvoice = $this->purchaseInvoice->create(array_merge($request->all(), [
            'invoice_number' => $this->generateInvoiceNumber(),
            'payment_method' => $request->payment_method,
            'payment_date' => $request->payment_date,
            'status' => $status,
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
            'clearing_payable_percentage' => $clearingPayablePercentage,
            'indebted_in_usd' => $indebtedInUsd,
            'indebted_in_riel' => $indebtedInRiel,
            'supplier_id' => $supplierId,
        ]));

        foreach ($rawMaterialsData as $material) {
            $material['purchase_invoice_id'] = $purchaseInvoice->id; 
            PurchaseInvoiceDetail::create($material);
        }
        
        return $purchaseInvoice;
    }


    public function update(int $id, Request $request): PurchaseInvoice
    {
        $purchaseInvoice = $this->purchaseInvoice -> with('purchaseInvoiceDetails.rawMaterial.supplier', 'purchaseInvoiceDetails.rawMaterial.category') ->findOrFail($id);

        $subTotalInRiel = 0;
        $subTotalInUsd = 0;
        $rawMaterialsData = [];
        $supplierId = null;

        foreach ($request->raw_materials as $rawMaterialId) {
            $rawMaterial = RawMaterial::withTrashed() -> findOrFail($rawMaterialId);
            
            if (is_null($supplierId)) {
                $supplierId = $rawMaterial->supplier_id;
            }elseif ($rawMaterial->supplier_id !== $supplierId) {
                throw new \Exception("Raw material ID {$rawMaterial->id} belongs to a different supplier. All raw materials must belong to the same supplier.");
            }

            $totalPriceInRiel = $rawMaterial->total_value_in_riel;
            $totalPriceInUsd = $rawMaterial->total_value_in_usd;

            $rawMaterialsData[] = [
                'quantity' => $rawMaterial->quantity,
                'total_price_in_riel' => $totalPriceInRiel,
                'total_price_in_usd' => $totalPriceInUsd,
                'raw_material_id' => $rawMaterial->id,
                // 'supplier_id' => $supplierId,
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
        $clearingPayablePercentage = $request->clearing_payable_percentage;
        $clearingPayableInUsd = ($clearingPayablePercentage / 100) * $grandTotalWithTaxInUsd;
        $clearingPayableInRiel = ($clearingPayablePercentage / 100) * $grandTotalWithTaxInRiel;
        $indebtedInUsd = max(0, $grandTotalWithTaxInUsd - $clearingPayableInUsd);
        $indebtedInRiel = max(0, $grandTotalWithTaxInRiel - $clearingPayableInRiel);

        $status = 'PAID';
        if ($clearingPayablePercentage == 0) {
            $status = 'UNPAID';
        } elseif ($clearingPayablePercentage < 100) {
            $status = 'INDEBTED';
        } elseif ($clearingPayablePercentage > 100) {
            $status = 'OVERPAID';
        }

        $purchaseInvoice->update(array_merge($request->all(), [
            'payment_method' => $request->payment_method,
            'payment_date' => $request->payment_date,
            'status' => $status,
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
            'clearing_payable_percentage' => $clearingPayablePercentage,
            'indebted_in_usd' => $indebtedInUsd,
            'indebted_in_riel' => $indebtedInRiel,
            'supplier_id' => $supplierId,
        ]));

        $existingRawMaterialIds = $purchaseInvoice->purchaseInvoiceDetails()->pluck('raw_material_id')->toArray();

        $newRawMaterialIds = array_column($rawMaterialsData, 'raw_material_id');
        $rawMaterialsToAdd = array_diff($newRawMaterialIds, $existingRawMaterialIds);
        $rawMaterialsToRemove = array_diff($existingRawMaterialIds, $newRawMaterialIds);

        foreach ($rawMaterialsData as $material) {
            if (in_array($material['raw_material_id'], $rawMaterialsToAdd)) {
                $material['purchase_invoice_id'] = $purchaseInvoice->id;
                PurchaseInvoiceDetail::create($material);
            }
        }

        if (!empty($rawMaterialsToRemove)) {
            PurchaseInvoiceDetail::where('purchase_invoice_id', $purchaseInvoice->id)
                ->whereIn('raw_material_id', $rawMaterialsToRemove)
                ->delete();
        }

        return $purchaseInvoice;
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
