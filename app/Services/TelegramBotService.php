<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramBotService
{
    protected string $token;
    protected string $apiUrl;

    public function __construct()
    {
        $this->token = config('services.telegram.bot_token');
        $this->apiUrl = "https://api.telegram.org/bot{$this->token}";
    }

    /**
     * Send a message
     */
    public function sendMessage($chatId, string $text, ?array $keyboard = null): ?array
    {
        $data = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ];

        if ($keyboard) {
            $data['reply_markup'] = json_encode($keyboard);
        }

        return $this->request('sendMessage', $data);
    }

    /**
     * Edit existing message
     */
    public function editMessage($chatId, $messageId, string $text, ?array $keyboard = null): ?array
    {
        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ];

        if ($keyboard) {
            $data['reply_markup'] = json_encode($keyboard);
        }

        return $this->request('editMessageText', $data);
    }

    /**
     * Answer callback query
     */
    public function answerCallback(string $callbackId, ?string $text = null, bool $showAlert = false): ?array
    {
        $data = [
            'callback_query_id' => $callbackId,
            'show_alert' => $showAlert,
        ];

        if ($text) {
            $data['text'] = $text;
        }

        return $this->request('answerCallbackQuery', $data);
    }

    /**
     * Set webhook URL
     */
    public function setWebhook(string $url): ?array
    {
        return $this->request('setWebhook', [
            'url' => $url,
            'allowed_updates' => ['message', 'callback_query'],
        ]);
    }

    /**
     * Get webhook info
     */
    public function getWebhookInfo(): ?array
    {
        return $this->request('getWebhookInfo');
    }

    /**
     * Delete webhook
     */
    public function deleteWebhook(): ?array
    {
        return $this->request('deleteWebhook');
    }

    /**
     * Send session started notification to client
     */
    public function notifyClientSessionStart($order): ?array
    {
        $customer = $order->customer;
        if (!$customer?->telegram_id) {
            return null;
        }

        $text = "🏃 <b>Seans boshlandi!</b>\n\n";
        $text .= "📋 Buyurtma: #{$order->order_number}\n";
        $text .= "💆 Master: {$order->master->full_name}\n";
        $text .= "📍 Master yo'lda...\n\n";
        $text .= "Muammo bo'lsa, quyidagi tugmani bosing.";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '❌ Muammo bor', 'callback_data' => "end_session:{$order->id}:client"],
                ],
            ],
        ];

        return $this->sendMessage($customer->telegram_id, $text, $keyboard);
    }

    /**
     * Send session started notification to master
     */
    public function notifyMasterSessionStart($order): ?array
    {
        $master = $order->master;
        if (!$master?->user?->telegram_id) {
            return null;
        }

        $text = "🏃 <b>Seans boshlandi!</b>\n\n";
        $text .= "📋 Buyurtma: #{$order->order_number}\n";
        $text .= "👤 Mijoz: {$order->customer->name}\n";
        $text .= "📍 Manzil: {$order->address}\n";
        $text .= "📞 Tel: {$order->contact_phone}\n\n";
        $text .= "Muammo bo'lsa, quyidagi tugmani bosing.";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '❌ Muammo bor', 'callback_data' => "end_session:{$order->id}:master"],
                ],
            ],
        ];

        return $this->sendMessage($master->user->telegram_id, $text, $keyboard);
    }

    /**
     * Send session completed notification to client (with rating)
     */
    public function notifyClientSessionEnd($order): ?array
    {
        $customer = $order->customer;
        if (!$customer?->telegram_id) {
            return null;
        }

        $text = "✅ <b>Seans tugadi!</b>\n\n";
        $text .= "📋 Buyurtma: #{$order->order_number}\n";
        $text .= "💆 Master: {$order->master->full_name}\n\n";
        $text .= "Iltimos, masterni baholang ⭐";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '⭐ Baholash', 'callback_data' => "rate_start:{$order->id}:client_to_master"],
                ],
            ],
        ];

        return $this->sendMessage($customer->telegram_id, $text, $keyboard);
    }

    /**
     * Send session completed notification to master (with rating)
     */
    public function notifyMasterSessionEnd($order): ?array
    {
        $master = $order->master;
        if (!$master?->user?->telegram_id) {
            return null;
        }

        $text = "✅ <b>Seans tugadi!</b>\n\n";
        $text .= "📋 Buyurtma: #{$order->order_number}\n";
        $text .= "👤 Mijoz: {$order->customer->name}\n\n";
        $text .= "Iltimos, mijozni baholang ⭐";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '⭐ Baholash', 'callback_data' => "rate_start:{$order->id}:master_to_client"],
                ],
            ],
        ];

        return $this->sendMessage($master->user->telegram_id, $text, $keyboard);
    }

    /**
     * Make API request
     */
    protected function request(string $method, array $data = []): ?array
    {
        try {
            $response = Http::post("{$this->apiUrl}/{$method}", $data);
            
            $result = $response->json();
            
            if (!($result['ok'] ?? false)) {
                Log::error('Telegram API error', [
                    'method' => $method,
                    'data' => $data,
                    'response' => $result,
                ]);
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Telegram API exception', [
                'method' => $method,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
