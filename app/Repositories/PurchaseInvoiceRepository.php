<?php

namespace App\Repositories;

use App\Models\PurchaseInvoice;
use App\Repositories\Interfaces\PurchaseInvoiceRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class PurchaseInvoiceRepository implements PurchaseInvoiceRepositoryInterface
{
    protected $purchaseInvoice;

    public function __construct(PurchaseInvoice $purchaseInvoice)
    {
        $this->purchaseInvoice = $purchaseInvoice;
    }

    /**
     * Build query with filters and includes
     * @return QueryBuilder
     */
    private function allBuilder(): QueryBuilder
    {
        return QueryBuilder::for(PurchaseInvoice::class)
            ->allowedIncludes(['details'])
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

    public function create(Request $request): PurchaseInvoice
    {
        $data = $request->all();
        $invoice = $this->purchaseInvoice->create($data);

        // Handle creation of invoice details
        foreach ($request->input('details', []) as $detailData) {
            $invoice->details()->create($detailData);
        }

        return $invoice;
    }

    public function update(int $id, Request $request): PurchaseInvoice
    {
        $invoice = $this->purchaseInvoice->findOrFail($id);
        $invoice->update($request->all());

        // Update invoice details
        $invoice->details()->delete();
        foreach ($request->input('details', []) as $detailData) {
            $invoice->details()->create($detailData);
        }

        return $invoice;
    }

    public function delete(int $id): void
    {
        $invoice = $this->purchaseInvoice->findOrFail($id);
        $invoice->details()->delete();
        $invoice->delete();
    }
}
