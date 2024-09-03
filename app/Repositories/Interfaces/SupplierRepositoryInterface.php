<?php

namespace App\Repositories\Interfaces;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

interface SupplierRepositoryInterface
{
    public function all(): LengthAwarePaginator;

    public function findById(int $id): Supplier;

    public function create(Request $request): Supplier;

    public function update(Request $request , int $id): Supplier;

    public function delete(int $id): void;
}
