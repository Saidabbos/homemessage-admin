<?php

namespace App\Services;

use App\Models\Master;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TherapistBotService
{
    protected ?string $token;
    protected string $apiUrl;

    public function __construct()
    {
        $this->token = config('services.telegram.therapist_bot_token');
        $this->apiUrl = $this->token ? "https://api.telegram.org/bot{$this->token}" : '';
    }

    /**
     * Check if bot is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->token);
    }

    /**
     * Send message to a chat
     */
    public function sendMessage(string $chatId, string $text, ?string $parseMode = 'Markdown', ?array $keyboard = null): bool
    {
        if (!$this->isConfigured()) {
            Log::warning('TherapistBot: Not configured');
            return false;
        }

        $params = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => $parseMode,
            'disable_web_page_preview' => true,
        ];

        if ($keyboard) {
            $params['reply_markup'] = json_encode($keyboard);
        }

        try {
            $response = Http::post("{$this->apiUrl}/sendMessage", $params);

            if ($response->successful() && $response->json('ok')) {
                return true;
            }

            Log::error('TherapistBot: Send failed', [
                'chat_id' => $chatId,
                'response' => $response->json(),
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('TherapistBot: Exception', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Send ASSIGNED notification to master
     */
    public function notifyAssigned(Order $order): bool
    {
        $master = $order->master;
        if (!$master || !$master->telegram_id) {
            return false;
        }

        $order->load(['serviceType', 'duration']);

        $date = $order->booking_date?->format('d.m.Y') ?? '-';
        $time = substr($order->arrival_window_start ?? '', 0, 5) . '–' . substr($order->arrival_window_end ?? '', 0, 5);
        $service = $order->serviceType?->name ?? '-';
        if (is_array($service)) {
            $service = $service['uz'] ?? $service['ru'] ?? array_values($service)[0] ?? '-';
        }
        $duration = $order->duration?->duration ?? '-';

        $message = "🆕 *YANGI BUYURTMA*\n\n"
            . "📋 #{$order->order_number}\n"
            . "📅 Sana: {$date}\n"
            . "🕐 Vaqt: {$time}\n"
            . "💆 Xizmat: {$service}\n"
            . "⏱ Davomiyligi: {$duration} daqiqa\n\n"
            . "📍 Manzil tasdiqlangandan keyin yuboriladi";

        return $this->sendMessage($master->telegram_id, $message);
    }

    /**
     * Send READY notification to master (full details)
     */
    public function notifyReady(Order $order): bool
    {
        $master = $order->master;
        if (!$master || !$master->telegram_id) {
            return false;
        }

        $order->load(['customer', 'serviceType', 'duration', 'oil']);

        $date = $order->booking_date?->format('d.m.Y') ?? '-';
        $time = substr($order->arrival_window_start ?? '', 0, 5) . '–' . substr($order->arrival_window_end ?? '', 0, 5);
        
        $service = $order->serviceType?->name ?? '-';
        if (is_array($service)) {
            $service = $service['uz'] ?? array_values($service)[0] ?? '-';
        }
        
        $oilName = $order->oil?->name ?? 'Standart';
        if (is_array($oilName)) {
            $oilName = $oilName['uz'] ?? array_values($oilName)[0] ?? 'Standart';
        }

        $duration = $order->duration?->duration ?? '-';
        $customer = $order->customer?->name ?? 'Noma\'lum';
        $phone = $order->conf_onsite_phone ?: $order->contact_phone ?: $order->customer?->phone ?? '-';
        $address = $order->address ?? 'Ko\'rsatilmagan';
        $people = $order->people_count ?? 1;

        $message = "✅ *TAYYOR - YO'LGA CHIQISH*\n\n"
            . "📋 #{$order->order_number}\n\n"
            . "👤 *Mijoz:* {$customer}\n"
            . "📞 *Tel:* {$phone}\n"
            . "📍 *Manzil:* {$address}\n";

        // Additional address details
        if ($order->conf_entrance || $order->conf_floor) {
            $message .= "🚪 Kirish: " . ($order->conf_entrance ?: '-') . ", Qavat: " . ($order->conf_floor ?: '-') . "\n";
        }
        if ($order->conf_landmark) {
            $message .= "🗺 Mo'ljal: {$order->conf_landmark}\n";
        }

        $message .= "\n📅 *Sana:* {$date}\n"
            . "🕐 *Kelish oynasi:* {$time}\n\n"
            . "💆 *Xizmat:* {$service}\n"
            . "⏱ *Davomiyligi:* {$duration} daqiqa\n"
            . "👥 *Odamlar:* {$people}\n"
            . "🧴 *Moy:* {$oilName}\n";

        if ($order->conf_note_to_master) {
            $message .= "\n📝 *Izoh:* {$order->conf_note_to_master}\n";
        }

        $message .= "\n🔗 " . url("/o/{$order->order_number}");

        // Inline keyboard with action buttons
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '📞 Qo\'ng\'iroq', 'url' => "tel:{$phone}"],
                    ['text' => '📍 Xarita', 'callback_data' => "map:{$order->id}"],
                ],
                [
                    ['text' => '✅ Boshladim', 'callback_data' => "start:{$order->id}"],
                ],
            ],
        ];

        return $this->sendMessage($master->telegram_id, $message, 'Markdown', $keyboard);
    }

    /**
     * Send CANCELLED notification to master
     */
    public function notifyCancelled(Order $order): bool
    {
        $master = $order->master;
        if (!$master || !$master->telegram_id) {
            return false;
        }

        $date = $order->booking_date?->format('d.m.Y') ?? '-';

        $message = "❌ *BUYURTMA BEKOR QILINDI*\n\n"
            . "📋 #{$order->order_number}\n"
            . "📅 Sana: {$date}\n";

        if ($order->cancellation_reason) {
            $message .= "📝 Sabab: {$order->cancellation_reason}";
        }

        return $this->sendMessage($master->telegram_id, $message);
    }

    /**
     * Send today's orders summary to master
     */
    public function sendTodayOrders(Master $master): bool
    {
        if (!$master->telegram_id) {
            return false;
        }

        $orders = Order::where('master_id', $master->id)
            ->where('booking_date', today())
            ->whereNotIn('status', [Order::STATUS_CANCELLED])
            ->orderBy('arrival_window_start')
            ->get();

        if ($orders->isEmpty()) {
            $message = "📅 *Bugun ({$this->formatDate(today())})*\n\n"
                . "Buyurtmalar yo'q. Dam oling! 😊";
        } else {
            $message = "📅 *Bugun ({$this->formatDate(today())})*\n\n"
                . "📋 *{$orders->count()} ta buyurtma:*\n\n";

            foreach ($orders as $i => $order) {
                $time = substr($order->arrival_window_start, 0, 5);
                $status = $this->getStatusEmoji($order->status);
                $message .= ($i + 1) . ". {$status} *{$time}* - {$order->address}\n";
            }
        }

        return $this->sendMessage($master->telegram_id, $message);
    }

    /**
     * Answer callback query
     */
    public function answerCallback(string $callbackId, ?string $text = null): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        $params = ['callback_query_id' => $callbackId];
        if ($text) {
            $params['text'] = $text;
        }

        try {
            $response = Http::post("{$this->apiUrl}/answerCallbackQuery", $params);
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Edit message
     */
    public function editMessage(string $chatId, int $messageId, string $text, ?array $keyboard = null): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        $params = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => 'Markdown',
        ];

        if ($keyboard) {
            $params['reply_markup'] = json_encode($keyboard);
        }

        try {
            $response = Http::post("{$this->apiUrl}/editMessageText", $params);
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function formatDate($date): string
    {
        return $date->format('d.m.Y');
    }

    protected function getStatusEmoji(string $status): string
    {
        return match ($status) {
            'NEW' => '🆕',
            'CONFIRMING' => '📞',
            'CONFIRMED' => '✅',
            'IN_PROGRESS' => '🔄',
            'COMPLETED' => '✔️',
            'CANCELLED' => '❌',
            default => '📋',
        };
    }
}
