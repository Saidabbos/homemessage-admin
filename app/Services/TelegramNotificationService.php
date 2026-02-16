<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramNotificationService
{
    protected ?string $botToken;
    protected ?string $dispatcherChatId;
    protected ?string $therapistBotToken;
    protected ?string $therapistChatId;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token');
        $this->dispatcherChatId = config('services.telegram.dispatcher_chat_id');
        $this->therapistBotToken = config('services.telegram.therapist_bot_token');
        $this->therapistChatId = config('services.telegram.therapist_group_id');
    }

    /**
     * Send new order notification to dispatchers group (OPS)
     */
    public function notifyNewOrder(Order $order, ?string $groupId = null): bool
    {
        $order->load(['customer', 'master', 'serviceType', 'duration']);

        $message = $this->buildNewOrderMessage($order, $groupId);

        return $this->sendMessage($this->dispatcherChatId, $message, $this->botToken);
    }

    /**
     * Send PAID notification to dispatchers group (OPS)
     */
    public function notifyPaid(Order $order): bool
    {
        $order->load(['master', 'serviceType']);

        $masterName = $order->master?->full_name ?? 'Noma\'lum';
        $date = $order->booking_date?->format('d.m.Y') ?? '-';
        $time = substr($order->arrival_window_start, 0, 5) . '–' . substr($order->arrival_window_end, 0, 5);
        $amount = number_format($order->total_amount, 0, '', ' ');

        $message = "✅ *TO'LANDI* | #{$order->order_number}\n\n"
            . "💰 {$amount} so'm\n"
            . "👨‍🔧 Usta: {$masterName}\n"
            . "📅 {$date} {$time}\n\n"
            . "🔗 Admin: " . url("/admin/orders/{$order->id}");

        return $this->sendMessage($this->dispatcherChatId, $message, $this->botToken);
    }

    /**
     * Send READY notification to therapists group
     * Full order details for master
     */
    public function notifyReady(Order $order): bool
    {
        if (empty($this->therapistBotToken) || empty($this->therapistChatId)) {
            Log::warning('Therapist notification skipped: missing bot token or chat ID');
            return false;
        }

        $order->load(['customer', 'master', 'serviceType', 'duration', 'oil']);

        $message = $this->buildReadyMessage($order);

        $result = $this->sendMessage($this->therapistChatId, $message, $this->therapistBotToken);

        if ($result) {
            $order->markReadySent();
        }

        return $result;
    }

    /**
     * Send notification for grouped orders (multi-master)
     */
    public function notifyGroupedOrders(array $orders, string $groupId): bool
    {
        if (empty($orders)) {
            return false;
        }

        $firstOrder = $orders[0];
        $firstOrder->load(['customer', 'serviceType', 'duration']);

        $masterNames = [];
        $totalAmount = 0;

        foreach ($orders as $order) {
            $order->load('master');
            $masterNames[] = $order->master?->full_name ?? 'Noma\'lum';
            $totalAmount += $order->total_amount;
        }

        $message = $this->buildGroupedOrderMessage($firstOrder, $masterNames, $totalAmount, count($orders));

        return $this->sendMessage($this->dispatcherChatId, $message, $this->botToken);
    }

    /**
     * Build NEW order message for OPS
     */
    protected function buildNewOrderMessage(Order $order, ?string $groupId = null): string
    {
        $customerName = $order->customer?->name ?? 'Noma\'lum';
        $customerPhone = $order->contact_phone ?? $order->customer?->phone ?? '-';
        $masterName = $order->master?->full_name ?? 'Noma\'lum';
        $serviceName = $order->serviceType?->name ?? 'Noma\'lum';
        $duration = $order->duration?->duration ?? 60;
        $date = $order->booking_date?->format('d.m.Y') ?? '-';
        $time = substr($order->arrival_window_start, 0, 5) . '–' . substr($order->arrival_window_end, 0, 5);
        $amount = number_format($order->total_amount, 0, '', ' ');

        $groupNote = $groupId ? "\n🔗 Guruh: " . substr($groupId, 0, 8) : '';

        return "🆕 *YANGI BUYURTMA*\n\n"
            . "📋 *{$order->order_number}*{$groupNote}\n\n"
            . "👤 Mijoz: {$customerName}\n"
            . "📞 Tel: {$customerPhone}\n\n"
            . "💆 Xizmat: {$serviceName}\n"
            . "⏱ Davomiyligi: {$duration} daqiqa\n"
            . "👨‍🔧 Usta: {$masterName}\n\n"
            . "📅 Sana: {$date}\n"
            . "🕐 Vaqt: {$time}\n\n"
            . "💰 Narxi: {$amount} so'm\n\n"
            . "🔗 Admin: " . url("/admin/orders/{$order->id}");
    }

    /**
     * Build READY message for THERAPISTS group
     * Contains full order details
     */
    protected function buildReadyMessage(Order $order): string
    {
        $masterName = $order->master?->full_name ?? 'Noma\'lum';
        $serviceName = $order->serviceType?->name ?? 'Noma\'lum';
        $oilName = $order->oil?->name ?? '-';
        $duration = $order->duration?->duration ?? 60;
        $date = $order->booking_date?->format('d.m.Y') ?? '-';
        $time = substr($order->arrival_window_start, 0, 5) . '–' . substr($order->arrival_window_end, 0, 5);
        $amount = number_format($order->total_amount, 0, '', ' ');

        // Customer info
        $customerPhone = $order->contact_phone ?? $order->customer?->phone ?? '-';
        $customerName = $order->customer?->name ?? '-';
        $onsitePhone = $order->conf_onsite_phone ?? '-';

        // Address info
        $address = $order->address ?? '-';
        $entrance = $order->conf_entrance ?? $order->entrance ?? '-';
        $floor = $order->conf_floor ?? $order->floor ?? '-';
        $elevator = $order->conf_elevator ? 'Ha' : 'Yo\'q';
        $parking = $order->conf_parking ?? '-';
        $landmark = $order->conf_landmark ?? $order->landmark ?? '-';

        // Restrictions
        $constraints = $order->conf_constraints ?? '-';
        $noteToMaster = $order->conf_note_to_master ?? '-';
        $spaceOk = $order->conf_space_ok ? 'Ha' : 'Yo\'q';
        $pets = $order->conf_pets ? 'Ha' : 'Yo\'q';

        // Master day view link
        $masterToken = $order->master?->token ?? '';
        $dayLink = $masterToken ? url("/m/{$masterToken}/day/" . $order->booking_date?->format('Y-m-d')) : '-';
        $orderLink = url("/o/{$order->order_number}");

        return "✅ *TAYYOR* | #{$order->order_number}\n\n"
            . "👨‍🔧 *Usta:* {$masterName}\n"
            . "📅 *Vaqt:* {$date} {$time} ({$duration} daq)\n"
            . "💆 *Massaj:* {$serviceName}\n"
            . ($oilName !== '-' ? "🧴 *Moy:* {$oilName}\n" : "")
            . "\n"
            . "📞 *Mijoz telefoni:* {$customerPhone}\n"
            . "👤 *Ismi:* {$customerName}\n"
            . "📱 *Joydagi telefon:* {$onsitePhone}\n"
            . "\n"
            . "📍 *Manzil*\n"
            . "{$address}\n"
            . "🚪 Kirish: {$entrance} | 🏢 Qavat: {$floor} | 🛗 Lift: {$elevator}\n"
            . "🅿️ Parking: {$parking}\n"
            . "🗺 Mo'ljal: {$landmark}\n"
            . "\n"
            . "⚠️ *Eslatmalar*\n"
            . "Cheklovlar: {$constraints}\n"
            . "Ustaga izoh: {$noteToMaster}\n"
            . "Joy 2×2: {$spaceOk} | Hayvonlar: {$pets}\n"
            . "\n"
            . "💰 *To'lov:* ✅ TO'LANGAN · {$amount} so'm\n"
            . "\n"
            . "🔗 *Havolalar:*\n"
            . "📆 Mening kunim: {$dayLink}\n"
            . "📋 Buyurtma: {$orderLink}";
    }

    /**
     * Build message for grouped orders (multi-master)
     */
    protected function buildGroupedOrderMessage(Order $firstOrder, array $masterNames, float $totalAmount, int $orderCount): string
    {
        $customerName = $firstOrder->customer?->name ?? 'Noma\'lum';
        $customerPhone = $firstOrder->contact_phone ?? $firstOrder->customer?->phone ?? '-';
        $serviceName = $firstOrder->serviceType?->name ?? 'Noma\'lum';
        $duration = $firstOrder->duration?->duration ?? 60;
        $date = $firstOrder->booking_date?->format('d.m.Y') ?? '-';
        $time = substr($firstOrder->arrival_window_start, 0, 5) . '–' . substr($firstOrder->arrival_window_end, 0, 5);
        $amount = number_format($totalAmount, 0, '', ' ');

        $mastersText = implode(', ', $masterNames);

        return "🆕 *YANGI GURUH BUYURTMA* ({$orderCount} kishi)\n\n"
            . "📋 *{$firstOrder->order_number}* va boshqalar\n\n"
            . "👤 Mijoz: {$customerName}\n"
            . "📞 Tel: {$customerPhone}\n\n"
            . "💆 Xizmat: {$serviceName}\n"
            . "⏱ Davomiyligi: {$duration} daqiqa (har biri)\n"
            . "👨‍🔧 Ustalar: {$mastersText}\n\n"
            . "📅 Sana: {$date}\n"
            . "🕐 Vaqt: {$time}\n\n"
            . "💰 Jami: {$amount} so'm\n\n"
            . "🔗 Admin: " . url("/admin/orders/{$firstOrder->id}");
    }

    /**
     * Send message via Telegram Bot API
     */
    protected function sendMessage(?string $chatId, string $text, ?string $botToken = null): bool
    {
        $token = $botToken ?? $this->botToken;

        if (empty($token) || empty($chatId)) {
            Log::warning('Telegram notification skipped: missing bot token or chat ID');
            return false;
        }

        try {
            $response = Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'Markdown',
                'disable_web_page_preview' => true,
            ]);

            if ($response->successful()) {
                Log::info('Telegram notification sent', ['chat_id' => $chatId]);
                return true;
            }

            Log::error('Telegram notification failed', [
                'chat_id' => $chatId,
                'response' => $response->json(),
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('Telegram notification error', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
