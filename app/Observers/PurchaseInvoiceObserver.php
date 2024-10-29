<?php

namespace App\Observers;

use App\Models\PurchaseInvoice;

class PurchaseInvoiceObserver
{
    /**
     * Handle the PurchaseInvoice "created" event.
     */
    public function created(PurchaseInvoice $purchaseInvoice): void
    {
        //
    }

    /**
     * Handle the PurchaseInvoice "updated" event.
     */
    public function updated(PurchaseInvoice $purchaseInvoice): void
    {
        // Calculate the clearing payable percentage based on clearing_payable and grand_total
        $clearingPayablePercentage = $purchaseInvoice->clearing_payable_percentage;
        
        // Calculate the indebted values based on the clearing payable and grand total
        $indebtedRiel = $purchaseInvoice->grand_total_with_tax_in_riel - ($clearingPayablePercentage / 100) * $purchaseInvoice->grand_total_with_tax_in_riel;
        $indebtedUsd = $purchaseInvoice->grand_total_with_tax_in_usd - ($clearingPayablePercentage / 100) * $purchaseInvoice->grand_total_with_tax_in_usd;
    
        if ($clearingPayablePercentage == 0) {
            // UNPAID if clearing payable percentage is 0
            $purchaseInvoice->status = 'UNPAID';
        } elseif ($clearingPayablePercentage > 0 && $clearingPayablePercentage < 100) {
            // INDEBTED if there is an outstanding amount with partial payment
            $purchaseInvoice->status = 'INDEBTED';
        } elseif ($clearingPayablePercentage == 100) {
            // PAID if clearing payable matches the grand total exactly
            $purchaseInvoice->status = 'PAID';
        } elseif ($clearingPayablePercentage > 100) {
            // OVERPAID if clearing payable is more than the grand total
            $purchaseInvoice->status = 'OVERPAID';
        }
    
        // Save the changes quietly to avoid triggering recursive events
        $purchaseInvoice->saveQuietly();
    }
    
    

    /**
     * Handle the PurchaseInvoice "deleted" event.
     */
    public function deleted(PurchaseInvoice $purchaseInvoice): void
    {
        //
    }

    /**
     * Handle the PurchaseInvoice "restored" event.
     */
    public function restored(PurchaseInvoice $purchaseInvoice): void
    {
        //
    }

    /**
     * Handle the PurchaseInvoice "force deleted" event.
     */
    public function forceDeleted(PurchaseInvoice $purchaseInvoice): void
    {
        //
    }
}
