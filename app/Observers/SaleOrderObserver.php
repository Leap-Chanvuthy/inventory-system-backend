<?php

namespace App\Observers;

use App\Models\SaleOrder;
use App\Services\TelegramNotificationService;

class SaleOrderObserver
{
    protected $telegram;

    public function __construct(TelegramNotificationService $telegram)
    {
        $this->telegram = $telegram;
    }

    /**
     * Handle the SaleOrder "created" event.
     */
    public function created(SaleOrder $saleOrder): void
    {
        // Construct the message
        $message = "🔔 *New Sale Order Created* 🔔\n";
        $message .= "----------------------------------\n";
        $message .= "*Sale Order ID:* {$saleOrder->id}\n";
        $message .= "*Sale Invoice:* {$saleOrder->sale_invoice_number}\n";
        $message .= "*Customer:* {$saleOrder->customer->fullname}\n";
        $message .= "*Customer Phone:* {$saleOrder->customer->phone_number}\n";
        $message .= "*Total Amount with Tax (USD):* {$saleOrder -> grand_total_with_tax_in_usd} $ \n";
        $message .= "*Total Amount with Tax (Riel):* {$saleOrder -> grand_total_with_tax_in_riel} ៛ \n";
        $message .= "*Payable Percentage:* {$saleOrder -> clearing_payable_percentage} (%) \n";
        $message .= "*Discount:* {$saleOrder -> discount_percentage} (%) |  {$saleOrder -> discount_value_in_riel} $ / {$saleOrder -> discount_value_in_riel} ៛\n";
        $message .= "*Tax:* {$saleOrder -> tax_percentage} (%) |  {$saleOrder -> tax_value_in_riel} $ / {$saleOrder -> tax_value_in_riel} ៛\n";
        $message .= "*Payment Status:* {$saleOrder -> payment_status}\n";
        $message .= "*Order Status:* {$saleOrder -> order_status}\n";
        $message .= "*Payment Method:* {$saleOrder -> payment_method}\n";
        $message .= "----------------------------------\n";

        $message .= "\n🔔 *ការបង្កើតការបញ្ជាទិញថ្មី* 🔔\n";
        $message .= "----------------------------------\n";
        $message .= "*លេខការបញ្ជាទិញ:* {$saleOrder->id}\n";
        $message .= "*លេខកូដវិក័យបត្រ:* {$saleOrder->sale_invoice_number}\n";
        $message .= "*អតិថិជន:* {$saleOrder->customer->fullname}\n";
        $message .= "*លេខទូរសព្ទ័អតិថិជន:* {$saleOrder->customer->phone_number}\n";
        $message .= "*តម្លៃសរុប និងអាករ (USD):* {$saleOrder -> grand_total_with_tax_in_usd} $ \n";
        $message .= "*តម្លៃសរុប និងអាករ (រៀល):* {$saleOrder -> grand_total_with_tax_in_riel} ៛ \n";
        $message .= "*ភាគរយការទូទាត់:* {$saleOrder -> clearing_payable_percentage}%\n";
        $message .= "*ការបញ្ចុះតម្លៃ:* {$saleOrder -> discount_percentage} (%) | {$saleOrder -> discount_value_in_riel} $ / {$saleOrder -> discount_value_in_riel} ៛\n";
        $message .= "*ពន្ធអាករ:* {$saleOrder -> tax_percentage} (%) | {$saleOrder -> tax_value_in_riel} $ / {$saleOrder -> tax_value_in_riel} ៛\n";
        $message .= "*ស្ថានភាពនៃការទូទាត់:* {$saleOrder -> payment_status}\n";
        $message .= "*ស្ថានភាពនៃការបញ្ជារទិញ:* {$saleOrder -> order_status}\n";
        $message .= "*វិធីសាស្រ្តនៃការទូទាត់:* {$saleOrder -> payment_method}\n";
        $message .= "----------------------------------";

        // Send the message via Telegram
        $this->telegram->sendMessage($message);
    }

    /**
     * Handle the SaleOrder "updated" event.
     */
    public function updated(SaleOrder $saleOrder): void
    {
        // Construct the message
        $message = "🔔 *New Sale Order Created* 🔔\n";
        $message .= "----------------------------------\n";
        $message .= "*Sale Order ID:* {$saleOrder->id}\n";
        $message .= "*Sale Invoice:* {$saleOrder->sale_invoice_number}\n";
        $message .= "*Customer:* {$saleOrder->customer->fullname}\n";
        $message .= "*Customer Phone:* {$saleOrder->customer->phone_number}\n";
        $message .= "*Total Amount with Tax (USD):* {$saleOrder -> grand_total_with_tax_in_usd} $ \n";
        $message .= "*Total Amount with Tax (Riel):* {$saleOrder -> grand_total_with_tax_in_riel} ៛ \n";
        $message .= "*Payable Percentage:* {$saleOrder -> clearing_payable_percentage} (%) \n";
        $message .= "*Discount:* {$saleOrder -> discount_percentage} (%) |  {$saleOrder -> discount_value_in_riel} $ / {$saleOrder -> discount_value_in_riel} ៛\n";
        $message .= "*Tax:* {$saleOrder -> tax_percentage} (%) |  {$saleOrder -> tax_value_in_riel} $ / {$saleOrder -> tax_value_in_riel} ៛\n";
        $message .= "*Payment Status:* {$saleOrder -> payment_status}\n";
        $message .= "*Order Status:* {$saleOrder -> order_status}\n";
        $message .= "*Payment Method:* {$saleOrder -> payment_method}\n";
        $message .= "----------------------------------\n";

        $message .= "\n🔔 *ការបង្កើតការបញ្ជាទិញថ្មី* 🔔\n";
        $message .= "----------------------------------\n";
        $message .= "*លេខការបញ្ជាទិញ:* {$saleOrder->id}\n";
        $message .= "*លេខកូដវិក័យបត្រ:* {$saleOrder->sale_invoice_number}\n";
        $message .= "*អតិថិជន:* {$saleOrder->customer->fullname}\n";
        $message .= "*លេខទូរសព្ទ័អតិថិជន:* {$saleOrder->customer->phone_number}\n";
        $message .= "*តម្លៃសរុប និងអាករ (USD):* {$saleOrder -> grand_total_with_tax_in_usd} $ \n";
        $message .= "*តម្លៃសរុប និងអាករ (រៀល):* {$saleOrder -> grand_total_with_tax_in_riel} ៛ \n";
        $message .= "*ភាគរយការទូទាត់:* {$saleOrder -> clearing_payable_percentage}%\n";
        $message .= "*ការបញ្ចុះតម្លៃ:* {$saleOrder -> discount_percentage} (%) | {$saleOrder -> discount_value_in_riel} $ / {$saleOrder -> discount_value_in_riel} ៛\n";
        $message .= "*ពន្ធអាករ:* {$saleOrder -> tax_percentage} (%) | {$saleOrder -> tax_value_in_riel} $ / {$saleOrder -> tax_value_in_riel} ៛\n";
        $message .= "*ស្ថានភាពនៃការទូទាត់:* {$saleOrder -> payment_status}\n";
        $message .= "*ស្ថានភាពនៃការបញ្ជារទិញ:* {$saleOrder -> order_status}\n";
        $message .= "*វិធីសាស្រ្តនៃការទូទាត់:* {$saleOrder -> payment_method}\n";
        $message .= "----------------------------------";

        // Send the message via Telegram
        $this->telegram->sendMessage($message);
    }
}