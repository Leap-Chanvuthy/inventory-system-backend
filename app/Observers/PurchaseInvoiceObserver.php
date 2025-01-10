<?php

namespace App\Observers;

use App\Models\PurchaseInvoice;
use App\Services\TelegramNotificationService;

class PurchaseInvoiceObserver
{
    protected $telegram;

    public function __construct(TelegramNotificationService $telegram)
    {
        $this->telegram = $telegram;
    }

    /**
     * Handle the PurchaseInvoice "created" event.
     */
    public function created(PurchaseInvoice $purchaseInvoice): void
    {
        // Construct the message
        $message = "🔔 *New Purchase Invoice Created* 🔔\n";
        $message .= "----------------------------------\n";
        $message .= "*Invoice ID:* {$purchaseInvoice->id}\n";
        $message .= "*Invoice Number:* {$purchaseInvoice->invoice_number}\n";
        $message .= "*Supplier:* {$purchaseInvoice->supplier->name} - {$purchaseInvoice->supplier->supplier_code}\n";
        $message .= "*Grand Total with Tax (USD):* {$purchaseInvoice->grand_total_with_tax_in_usd}\n";
        $message .= "*Grand Total with Tax (Riel):* {$purchaseInvoice->grand_total_with_tax_in_riel}\n";
        $message .= "*Payable​ Rate:* {$purchaseInvoice->clearing_payable_percentage}%\n";
        $message .= "*Discount:* {$purchaseInvoice -> discount_percentage} (%) |  {$purchaseInvoice -> discount_value_in_riel} $ / {$purchaseInvoice -> discount_value_in_riel} ៛ %\n";
        $message .= "*Tax:* {$purchaseInvoice -> tax_percentage} (%) |  {$purchaseInvoice -> tax_value_in_usd} $ / {$purchaseInvoice -> tax_value_in_riel} ៛ %\n";
        $message .= "*Indebted Money:* {$purchaseInvoice -> indebted_in_usd} $ / {$purchaseInvoice -> indebted_in_riel} ៛ %\n";
        $message .= "*Status:* {$purchaseInvoice->status}\n";

        $message .= "\n🔔 *វិក្កយបត្រទិញថ្មីត្រូវបានបង្កើត* 🔔\n";
        $message .= "----------------------------------\n";
        $message .= "*លេខវិក្កយបត្រ:* {$purchaseInvoice->id}\n";
        $message .= "*លេខវិក្កយបត្រ:* {$purchaseInvoice->invoice_number}\n";
        $message .= "*អ្នកផ្គត់ផ្គង់:* {$purchaseInvoice->supplier->name} - {$purchaseInvoice->supplier->supplier_code}\n";
        $message .= "*តម្លៃសរុប និងអាករ (USD):* {$purchaseInvoice->grand_total_with_tax_in_usd}\n";
        $message .= "*តម្លៃសរុប និងអាករ (រៀល):* {$purchaseInvoice->grand_total_with_tax_in_riel}\n";
        $message .= "*ភាគរយការទូទាត់:* {$purchaseInvoice->clearing_payable_percentage}%\n";
        $message .= "*ការបញ្ចុះតម្លៃ:* {$purchaseInvoice -> discount_percentage} |  {$purchaseInvoice -> discount_value_in_riel} $ / {$purchaseInvoice -> discount_value_in_riel} ៛ %\n";
        $message .= "*ពន្ធអាករ:* {$purchaseInvoice -> tax_percentage} |  {$purchaseInvoice -> tax_value_in_usd} $ / {$purchaseInvoice -> tax_value_in_riel} ៛ %\n";
        $message .= "*លុយជំពាក់:* {$purchaseInvoice -> indebted_in_usd} $ / {$purchaseInvoice -> indebted_in_riel} ៛ %\n";
        $message .= "*ស្ថានភាពនៃការទូរទាត់:* {$purchaseInvoice->status}\n";
        $message .= "----------------------------------";

        // Send the message via Telegram
        $this->telegram->sendMessage($message);
    }

    /**
     * Handle the PurchaseInvoice "updated" event.
     */
    public function updated(PurchaseInvoice $purchaseInvoice): void
    {
        // Calculate the clearing payable percentage based on clearing_payable and grand_total
        $clearingPayablePercentage = $purchaseInvoice->clearing_payable_percentage;

        // Determine the status based on the clearing payable percentage
        if ($clearingPayablePercentage == 0) {
            $purchaseInvoice->status = 'UNPAID';
        } elseif ($clearingPayablePercentage > 0 && $clearingPayablePercentage < 100) {
            $purchaseInvoice->status = 'INDEBTED';
        } elseif ($clearingPayablePercentage == 100) {
            $purchaseInvoice->status = 'PAID';
        } elseif ($clearingPayablePercentage > 100) {
            $purchaseInvoice->status = 'OVERPAID';
        }

        // Save the updated status quietly
        $purchaseInvoice->saveQuietly();

        // Construct the message
        $message = "🔔 *Purchase Invoice Update* 🔔\n";
        $message .= "----------------------------------\n";
        $message .= "*Invoice ID:* {$purchaseInvoice->id}\n";
        $message .= "*Invoice Number:* {$purchaseInvoice->invoice_number}\n";
        $message .= "*Supplier:* {$purchaseInvoice->supplier->name} - {$purchaseInvoice->supplier->supplier_code}\n";
        $message .= "*Grand Total with Tax (USD):* {$purchaseInvoice->grand_total_with_tax_in_usd}\n";
        $message .= "*Grand Total with Tax (Riel):* {$purchaseInvoice->grand_total_with_tax_in_riel}\n";
        $message .= "*Payable Percentage:* {$clearingPayablePercentage}%\n";
        $message .= "*Discount:* {$purchaseInvoice -> discount_percentage} (%) |  {$purchaseInvoice -> discount_value_in_riel} $ / {$purchaseInvoice -> discount_value_in_riel} ៛ %\n";
        $message .= "*Tax:* {$purchaseInvoice -> tax_percentage} (%) |  {$purchaseInvoice -> tax_value_in_usd} $ / {$purchaseInvoice -> tax_value_in_riel} ៛ %\n";
        $message .= "*Indebted Money:* {$purchaseInvoice -> indebted_in_usd} $ / {$purchaseInvoice -> indebted_in_riel} ៛ %\n";
        $message .= "*Status:* {$purchaseInvoice->status}\n";

        $message .= "\n🔔 *ការផ្លាស់ប្តូរតម្លៃវិក្កយបត្រ* 🔔\n";
        $message .= "----------------------------------\n";
        $message .= "*លេខសម្គាល់វិក្កយបត្រ:* {$purchaseInvoice->id}\n";
        $message .= "*លេខវិក្កយបត្រ:* {$purchaseInvoice->invoice_number}\n";
        $message .= "*អ្នកផ្គត់ផ្គង់:* {$purchaseInvoice->supplier->name} - {$purchaseInvoice->supplier->supplier_code}\n";
        $message .= "*តម្លៃសរុប និងអាករ (USD):* {$purchaseInvoice->grand_total_with_tax_in_usd}\n";
        $message .= "*តម្លៃសរុប និងអាករ (រៀល):* {$purchaseInvoice->grand_total_with_tax_in_riel}\n";
        $message .= "*ភាគរយការទូទាត់:* {$clearingPayablePercentage}%\n";
        $message .= "*ការបញ្ចុះតម្លៃ:* {$purchaseInvoice -> discount_percentage} |  {$purchaseInvoice -> discount_value_in_riel} $ / {$purchaseInvoice -> discount_value_in_riel} ៛ %\n";
        $message .= "*ពន្ធអាករ:* {$purchaseInvoice -> tax_percentage} |  {$purchaseInvoice -> tax_value_in_usd} $ / {$purchaseInvoice -> tax_value_in_riel} ៛ %\n";
        $message .= "*លុយជំពាក់:* {$purchaseInvoice -> indebted_in_usd} $ / {$purchaseInvoice -> indebted_in_riel} ៛ %\n";
        $message .= "*ស្ថានភាពនៃការទូទាត់:* {$purchaseInvoice->status}\n";
        $message .= "----------------------------------";

        // Send the message via Telegram
        $this->telegram->sendMessage($message);
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
