<?php

namespace App\Observers;

use App\Models\RawMaterial;

class RawMaterialObserver
{
    /**
     * Handle the RawMaterial "created" event.
     */
    public function created(RawMaterial $rawMaterial): void
    {
        //
    }

    /**
     * Handle the RawMaterial "updated" event.
     */
    public function updated(RawMaterial $rawMaterial)
    {
        if ($rawMaterial->remaining_quantity == 0) {
            $rawMaterial->status = 'OUT_OF_STOCK';
        } elseif ($rawMaterial->remaining_quantity > 0) {
            $rawMaterial->status = 'IN_STOCK';
        }

        $rawMaterial->saveQuietly();
    }
    

    /**
     * Handle the RawMaterial "deleted" event.
     */
    public function deleted(RawMaterial $rawMaterial): void
    {
        //
    }

    /**
     * Handle the RawMaterial "restored" event.
     */
    public function restored(RawMaterial $rawMaterial): void
    {
        //
    }

    /**
     * Handle the RawMaterial "force deleted" event.
     */
    public function forceDeleted(RawMaterial $rawMaterial): void
    {
        //
    }
}
