<?php

namespace App\Observers;

use App\Models\Product;

class ProductObserver
{
    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        if ($product->remaining_quantity == 0) {
            $product->status = 'OUT_OF_STOCK';
        } elseif ($product->remaining_quantity > 0 && $product->remaining_quantity == $product->minimum_stock_level) {
            $product->status = 'LOW_STOCK';
        } else {
            $product->status = 'IN_STOCK';
        }

        $product->saveQuietly();
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "force deleted" event.
     */
    public function forceDeleted(Product $product): void
    {
        //
    }
}
