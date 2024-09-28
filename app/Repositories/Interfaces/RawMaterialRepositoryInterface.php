<?php

namespace App\Repositories\Interfaces;

use App\Models\RawMaterial;
use App\Http\Requests\StoreRawMaterialRequest;
use Illuminate\Pagination\LengthAwarePaginator;

interface RawMaterialRepositoryInterface
{
    public function all(): LengthAwarePaginator;
    public function findById(int $id): RawMaterial;
    public function create(StoreRawMaterialRequest $request): RawMaterial; 
    public function update(int $id, StoreRawMaterialRequest $request): RawMaterial; 
    public function delete(int $id): void;
}
