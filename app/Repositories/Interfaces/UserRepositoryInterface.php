<?php

namespace App\Repositories\Interfaces;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface {
    public function all(): LengthAwarePaginator;

    public function findById(int $id): User;

    public function create (Request $request): User;

    public function update (int $id , Request $request): User;

    public function delete (int $id): void;
}