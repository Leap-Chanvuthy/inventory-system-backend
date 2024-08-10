<?php
namespace App\Repositories;

use App\Models\Supplier;
use App\Repositories\Interfaces\SupplierRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class SupplierRepository implements SupplierRepositoryInterface
{
    protected $supplier;

    public function __construct(Supplier $supplier)
    {
        $this->supplier = $supplier;
    }

    /**
     * Build query with filters and includes
     * @return QueryBuilder
     */
    private function allBuilder(): QueryBuilder
    {
        return QueryBuilder::for(Supplier::class)
            ->allowedIncludes(['inventories'])
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('location'),
                AllowedFilter::partial('name'),
                AllowedFilter::callback('phone_number', function ($query, $value) {
                    $query->where('phone_number', 'LIKE', "%{$value}%");
                }),
            ])
            ->allowedSorts('created_at', 'updated_at', 'name', 'location')
            ->defaultSort('-created_at');
    }

    public function all(): LengthAwarePaginator
    {
        return $this->allBuilder()->paginate(10);
    }

    public function findById(int $id): Supplier
    {
        return Supplier::with('inventories')->findOrFail($id);
    }

    public function create(Request $request): Supplier
    {
        return Supplier::create($request->all());
    }

    public function update(int $id, Request $request): Supplier
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->update($request->all());
        return $supplier;
    }

    public function delete(int $id): void
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();
    }
}
