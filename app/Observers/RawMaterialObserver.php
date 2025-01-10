<?php

namespace App\Observers;

use App\Models\RawMaterial;
use App\Services\TelegramNotificationService;

class RawMaterialObserver
{
    /**
     * Handle the RawMaterial "created" event.
     */
    protected $telegram;

    public function __construct(TelegramNotificationService $telegram)
    {
        $this->telegram = $telegram;
    }

    /**
     * Handle the RawMaterial "created" event.
     */
    public function created(RawMaterial $rawMaterial): void
    {
        // You can add a message for raw material creation if needed
    }

    /**
     * Handle the RawMaterial "updated" event.
     */
    public function updated(RawMaterial $rawMaterial): void
    {

        if ($rawMaterial->remaining_quantity == 0) {
            $rawMaterial->status = 'OUT_OF_STOCK';
        } elseif ($rawMaterial->remaining_quantity > 0 && $rawMaterial->remaining_quantity <= $rawMaterial->minimum_stock_level) {
            $rawMaterial->status = 'LOW_STOCK';
        } else {
            $rawMaterial->status = 'IN_STOCK';
        }

        $rawMaterial->saveQuietly();

        // Construct the message
        $message = "🔔 *Raw Material Stock Update* 🔔\n";
        $message .= "----------------------------------\n";
        $message .= "*Raw Material ID:* {$rawMaterial->id}\n";
        $message .= "*Raw Material Code:* {$rawMaterial->material_code}\n";
        $message .= "*Raw Material Name:* {$rawMaterial->name}\n";
        $message .= "*Remaining Quantity:* {$rawMaterial->remaining_quantity}\n";

        $message .= "\n🔔 *ការផ្លាស់ប្តូរស្តុកវត្ថុធាតុដើម* 🔔\n";
        $message .= "----------------------------------\n";
        $message .= "*លេខសម្គាល់វត្ថុធាតុដើម:* {$rawMaterial->id}\n";
        $message .= "*លេខកូដវត្ថុធាតុដើម:* {$rawMaterial->material_code}\n";
        $message .= "*ឈ្មោះវត្ថុធាតុដើម:* {$rawMaterial->name}\n";
        $message .= "*បរិមាណដែលនៅសល់:* {$rawMaterial->remaining_quantity}\n";

        if ($rawMaterial->remaining_quantity == 0) {
            $message .= "\n";
            $message .= "*Status:* 🚨 OUT OF STOCK 🚨\n";
            $message .= "⚠️ Please restock this raw material as soon as possible.\n";

            $message .= "\n";
            $message .= "*ស្ថានភាព:* 🚨 អស់ពីស្តុក 🚨\n";
            $message .= "⚠️ សូមបំពេញស្តុកវត្ថុធាតុដើមនេះឱ្យបានឆាប់តាមដែលអាចធ្វើទៅបាន។\n";
        } elseif ($rawMaterial->remaining_quantity > 0 && $rawMaterial->remaining_quantity <= $rawMaterial->minimum_stock_level) {
            $message .= "\n";
            $message .= "*Status:* ⚠️ LOW STOCK ⚠️\n";
            $message .= "⚠️ The stock for this raw material is running low. Consider restocking soon.\n";

            $message .= "\n";
            $message .= "*ស្ថានភាព:* ⚠️ ស្តុកទាប ⚠️\n";
            $message .= "⚠️ ស្តុកវត្ថុធាតុដើមនេះកំពុងតែអស់។ សូមពិចារណាបំពេញស្តុកឆាប់ៗនេះ។\n";
        } else {
            $message .= "*Status:* ✅ IN STOCK ✅\n";

            $message .= "*ស្ថានភាព:* ✅ មានស្តុក ✅\n";
        }

        $message .= "----------------------------------";

        // Send the message via Telegram
        $result = $this->telegram->sendMessage($message);
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
