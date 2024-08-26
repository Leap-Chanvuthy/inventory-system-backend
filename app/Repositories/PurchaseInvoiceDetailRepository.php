<?php

namespace App\Repositories;

use App\Models\PurchaseInvoiceDetail;
use App\Repositories\Interfaces\PurchaseInvoiceDetailRepositoryInterface;
use Illuminate\Http\Request;

class PurchaseInvoiceDetailRepository implements PurchaseInvoiceDetailRepositoryInterface
{
    protected $purchaseInvoiceDetail;

    public function __construct(PurchaseInvoiceDetail $purchaseInvoiceDetail)
    {
        $this-> purchaseInvoiceDetail = $purchaseInvoiceDetail;
    }

    public function all()
    {
        return $this->purchaseInvoiceDetail->all();
    }

    public function findById(int $id): PurchaseInvoiceDetail
    {
        return $this->purchaseInvoiceDetail->findOrFail($id);
    }

    public function create(Request $request): PurchaseInvoiceDetail
    {
        return $this->purchaseInvoiceDetail->create($request->all());
    }

    public function update(int $id, Request $request): PurchaseInvoiceDetail
    {
        $invoiceDetail = $this->purchaseInvoiceDetail->findOrFail($id);
        $invoiceDetail->update($request->all());
        return $invoiceDetail;
    }

    public function delete(int $id): void
    {
        $invoiceDetail = $this->purchaseInvoiceDetail->findOrFail($id);
        $invoiceDetail->delete();
    }
}
