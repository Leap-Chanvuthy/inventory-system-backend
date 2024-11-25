<?php

namespace App\Repositories\Interfaces;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    public function all(): LengthAwarePaginator;
    public function trashed(): LengthAwarePaginator;
    public function findById(int $id): Product;
    public function create(Request $request): Product; 
    public function update(int $id, Request $request): Product; 
    public function delete(int $id): void;
}