<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\TelegramNotificationService;

class ProductObserver
{
    protected $telegram;

    public function __construct(TelegramNotificationService $telegram)
    {
        $this->telegram = $telegram;
    }

    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        // Construct the message
        $message = "🔔 *New Product Created* 🔔\n";
        $message .= "----------------------------------\n";
        $message .= "*Product ID:* {$product->id}\n";
        $message .= "*Product Code:* {$product->product_code}\n";
        $message .= "*Product Name:* {$product->product_name}\n";
        $message .= "*Initial Quantity:* {$product->initial_quantity}\n";
        $message .= "*Remaining Quantity:* {$product->remaining_quantity}\n";
        $message .= "*Status:* {$product->status}\n";

        $message .= "\n🔔 *ផលិតផលថ្មីត្រូវបានបង្កើត* 🔔\n";
        $message .= "----------------------------------\n";
        $message .= "*លេខសម្គាល់ផលិតផល:* {$product->id}\n";
        $message .= "*លេខកូដផលិតផល:* {$product->product_code}\n";
        $message .= "*ឈ្មោះផលិតផល:* {$product->product_name}\n";
        $message .= "*បរិមាណដើម:* {$product->initial_quantity}\n";
        $message .= "*បរិមាណដែលនៅសល់:* {$product->remaining_quantity}\n";
        $message .= "*ស្ថានភាព:* {$product->status}\n";
        $message .= "----------------------------------";

        // Send the message via Telegram
        $result = $this->telegram->sendMessage($message);
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        $message = "🔔 *Product Stock Update* 🔔\n";
        $message .= "----------------------------------\n";
        $message .= "*Product ID:* {$product->id}\n";
        $message .= "*Product Code:* {$product->product_code}\n";
        $message .= "*Product Name:* {$product->product_name}\n";
        $message .= "*Remaining Quantity:* {$product->remaining_quantity}\n";

        $message .= "\n🔔 *ការផ្លាស់ប្តូរស្តុកផលិតផល* 🔔\n";
        $message .= "----------------------------------\n";
        $message .= "*លេខសម្គាល់ផលិតផល:* {$product->id}\n";
        $message .= "*លេខកូដផលិតផល:* {$product->product_code}\n";
        $message .= "*ឈ្មោះផលិតផល:* {$product->product_name}\n";
        $message .= "*បរិមាណដែលនៅសល់:* {$product->remaining_quantity}\n";

        if ($product->remaining_quantity == 0) {
            $product->status = 'OUT_OF_STOCK';
            $message .= "\n";
            $message .= "*Status:* 🚨 OUT OF STOCK 🚨\n";
            $message .= "⚠️ Please restock this product as soon as possible.\n";

            $message .= "\n";
            $message .= "*ស្ថានភាព:* 🚨 អស់ពីស្តុក 🚨\n";
            $message .= "⚠️ សូមបំពេញស្តុកផលិតផលនេះឱ្យបានឆាប់តាមដែលអាចធ្វើទៅបាន។\n";
        } elseif ($product->remaining_quantity > 0 && $product->remaining_quantity <= $product->minimum_stock_level) {
            $product->status = 'LOW_STOCK';
            $message .= "\n";
            $message .= "*Status:* ⚠️ LOW STOCK ⚠️\n";
            $message .= "⚠️ The stock for this product is running low. Consider restocking soon.\n";

            $message .= "\n";
            $message .= "*ស្ថានភាព:* ⚠️ ស្តុកទាប ⚠️\n";
            $message .= "⚠️ ស្តុកផលិតផលនេះកំពុងតែអស់។ សូមពិចារណាបំពេញស្តុកឆាប់ៗនេះ។\n";
        } else {
            $product->status = 'IN_STOCK';
            $message .= "*Status:* ✅ IN STOCK ✅\n";

            $message .= "*ស្ថានភាព:* ✅ មានស្តុក ✅\n";
        }

        $message .= "----------------------------------";

        $product->saveQuietly();

        // Send the message via Telegram
        $this->telegram->sendMessage($message);
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
