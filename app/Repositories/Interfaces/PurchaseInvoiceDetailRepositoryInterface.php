<?php

namespace App\Repositories\Interfaces;

use App\Models\PurchaseInvoiceDetail;
use Illuminate\Http\Request;

interface PurchaseInvoiceDetailRepositoryInterface
{
    public function all();

    public function findById(int $id): PurchaseInvoiceDetail;

    public function create(Request $request): PurchaseInvoiceDetail;

    public function update(int $id, Request $request): PurchaseInvoiceDetail;

    public function delete(int $id): void;
}
