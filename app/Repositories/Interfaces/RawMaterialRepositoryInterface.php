<?php

namespace App\Repositories\Interfaces;

use App\Models\RawMaterial;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

interface RawMaterialRepositoryInterface
{
    public function all(): LengthAwarePaginator;
    public function findById(int $id): RawMaterial;
    public function create(Request $request): array;
    public function update(int $id, Request $request): RawMaterial;
    public function delete(int $id): void;
}
