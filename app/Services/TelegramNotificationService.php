<?php
namespace App\Services;

use GuzzleHttp\Client;

class TelegramNotificationService
{
    protected $client;
    protected $botToken;
    protected $chatId;

    public function __construct()
    {
        $this->client = new Client();
        $this->botToken = env('TELEGRAM_BOT_TOKEN');
        $this->chatId = env('TELEGRAM_CHAT_ID');
    }

    public function sendMessage($message)
    {
        $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";

        $response = $this->client->post($url, [
            'form_params' => [
                'chat_id' => $this->chatId,
                'text' => $message,
            ],
        ]);

        return json_decode($response->getBody(), true);
    }
}