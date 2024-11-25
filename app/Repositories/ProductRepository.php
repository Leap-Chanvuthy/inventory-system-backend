<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\RawMaterial;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Builder;

class ProductRepository implements ProductRepositoryInterface
{
    protected $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * Build query with filters and includes
     * @return QueryBuilder
     */
    private function allBuilder(): QueryBuilder
    {
        return QueryBuilder::for(Product::class)
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('product_category_id'),
                AllowedFilter::exact('status'),
                AllowedFilter::callback('search', function (Builder $query, $value) {
                    $query->where(function ($query) use ($value) {
                        $query->where('product_name', 'LIKE', "%{$value}%")
                            ->orWhere('product_code', 'LIKE', "%{$value}%")
                            ->orWhere('unit_price_in_usd', 'LIKE', "%{$value}%")
                            ->orWhere('total_value_in_usd', 'LIKE', "%{$value}%")
                            ->orWhere('warehouse_location', 'LIKE', "%{$value}%")
                            ->orWhere('status', 'LIKE', "%{$value}%");
                    });
                }),
            ])
            ->allowedSorts('created_at', 'updated_at', 'product_name')
            ->defaultSort('-created_at');
    }

    private function allBuilderWithTrashed(): QueryBuilder
    {
        return QueryBuilder::for(Product::class)
            ->onlyTrashed() 
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('product_category_id'),
                AllowedFilter::exact('status'),
                AllowedFilter::callback('search', function (Builder $query, $value) {
                    $query->where(function ($query) use ($value) {
                        $query->where('product_name', 'LIKE', "%{$value}%")
                            ->orWhere('product_code', 'LIKE', "%{$value}%")
                            ->orWhere('unit_price_in_usd', 'LIKE', "%{$value}%")
                            ->orWhere('total_value_in_usd', 'LIKE', "%{$value}%")
                            ->orWhere('warehouse_location', 'LIKE', "%{$value}%")
                            ->orWhere('status', 'LIKE', "%{$value}%");
                    });
                }),
            ])
            ->allowedSorts('created_at', 'updated_at', 'product_name');
    }

    
    public function generateRawMaterialCode(): string
    {
        $lastProduct = Product::withTrashed()
            ->selectRaw('MAX(CAST(SUBSTRING(product_code, 5) AS UNSIGNED)) AS max_code')
            ->first();

        $lastCode = $lastProduct->max_code ?? 0;

        $newNumber = str_pad($lastCode + 1, 6, '0', STR_PAD_LEFT);
        return 'PRODUCT-' . $newNumber;
    }


    public function all(): LengthAwarePaginator
    {
        return $this->allBuilder()->with('product_images')->paginate(10);
    }


    public function trashed(): LengthAwarePaginator
    {
    }


    public function findById(int $id): Product
    {
    }




    public function create(Request $request): Product
    {


    }


    public function update(int $id, Request $request): Product
    {

    }


    public function delete(int $id): void
    {

    }



}
