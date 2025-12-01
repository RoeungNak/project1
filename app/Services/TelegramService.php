<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TelegramService
{
    protected $token;
    protected $chatId;

    public function __construct()
    {
        $this->token = config('services.telegram.bot_token');
        $this->chatId = config('services.telegram.chat_id');
    }

    // Send text
    public function sendMessage($message)
    {
        return Http::post("https://api.telegram.org/bot{$this->token}/sendMessage", [
            'chat_id' => $this->chatId,
            'text'    => $message,
            'parse_mode' => 'HTML',
        ]);
    }

    // Send photo with caption
    public function sendPhoto($photoPath, $caption = null)
    {
        return Http::attach(
            'photo',
            file_get_contents($photoPath),
            basename($photoPath)
        )->post("https://api.telegram.org/bot{$this->token}/sendPhoto", [
            'chat_id' => $this->chatId,
            'caption' => $caption,
            'parse_mode' => 'HTML',
        ]);
    }
}
