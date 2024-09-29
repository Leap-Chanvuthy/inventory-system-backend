<?php

namespace App\Repositories\Interfaces;

use App\Models\PurchaseInvoice;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

interface PurchaseInvoiceRepositoryInterface
{
    public function all(): LengthAwarePaginator;

    public function findById(int $id): PurchaseInvoice;

    public function create(Request $request): PurchaseInvoice;

    public function update(int $id, Request $request): PurchaseInvoice;

    public function delete(int $id): void;

    public function restore(int $id): PurchaseInvoice;

    public function generateInvoiceNumber(): string;
}
