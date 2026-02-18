<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Master;
use App\Models\Order;
use App\Services\TherapistBotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TherapistBotController extends Controller
{
    public function __construct(
        protected TherapistBotService $bot
    ) {}

    /**
     * Handle incoming webhook from Telegram
     */
    public function webhook(Request $request)
    {
        $update = $request->all();

        Log::info('TherapistBot webhook received', ['update' => $update]);

        // Handle callback queries (button clicks)
        if (isset($update['callback_query'])) {
            return $this->handleCallback($update['callback_query']);
        }

        // Handle messages
        if (isset($update['message'])) {
            return $this->handleMessage($update['message']);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Handle callback query (inline button click)
     */
    protected function handleCallback(array $callback)
    {
        $data = $callback['data'] ?? '';
        $chatId = $callback['message']['chat']['id'] ?? null;
        $messageId = $callback['message']['message_id'] ?? null;
        $userId = $callback['from']['id'] ?? null;

        // Parse callback data: action:param
        [$action, $param] = array_pad(explode(':', $data, 2), 2, null);

        Log::info('TherapistBot callback', ['action' => $action, 'param' => $param]);

        // Acknowledge callback
        $this->bot->answerCallback($callback['id']);

        switch ($action) {
            case 'start':
                return $this->handleStartSession($param, $chatId, $messageId, $userId);

            case 'complete':
                return $this->handleCompleteSession($param, $chatId, $messageId, $userId);

            case 'map':
                $this->bot->answerCallback($callback['id'], 'Xarita tez orada...');
                break;
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Handle /start command and contact sharing
     */
    protected function handleMessage(array $message)
    {
        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? '';
        $telegramId = (string) $message['from']['id'];

        // Handle contact sharing
        if (isset($message['contact'])) {
            return $this->handleContact($message);
        }

        // Handle commands
        if (str_starts_with($text, '/start')) {
            return $this->handleStart($message);
        }

        if (str_starts_with($text, '/status')) {
            return $this->handleStatus($message);
        }

        if (str_starts_with($text, '/orders') || str_starts_with($text, '/bugun')) {
            return $this->handleTodayOrders($message);
        }

        if (str_starts_with($text, '/help') || str_starts_with($text, '/yordam')) {
            return $this->handleHelp($message);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Handle /start command
     */
    protected function handleStart(array $message)
    {
        $chatId = $message['chat']['id'];
        $telegramId = (string) $message['from']['id'];
        $firstName = $message['from']['first_name'] ?? 'Salom';

        // Check if already registered
        $master = Master::where('telegram_id', $telegramId)->first();

        if ($master) {
            $this->bot->sendMessage($chatId,
                "👋 Salom, *{$master->full_name}*!\n\n"
                . "✅ Siz allaqachon ro'yxatdan o'tgansiz.\n\n"
                . "📲 *Buyruqlar:*\n"
                . "/orders - Bugungi buyurtmalar\n"
                . "/status - Hisobingiz holati\n"
                . "/help - Yordam"
            );
            return response()->json(['ok' => true]);
        }

        $text = "👋 Salom, *{$firstName}*!\n\n"
            . "🏠 *HomeMessage* massajchilar boti.\n\n"
            . "🔗 Hisobingizni ulash uchun quyidagi tugmani bosib *telefon raqamingizni* yuboring.\n\n"
            . "📱 Raqamingiz tizimda ro'yxatdan o'tgan bo'lishi kerak.";

        $keyboard = [
            'keyboard' => [
                [
                    ['text' => '📱 Telefon raqamni yuborish', 'request_contact' => true]
                ]
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => true,
        ];

        $this->bot->sendMessage($chatId, $text, 'Markdown', $keyboard);

        return response()->json(['ok' => true]);
    }

    /**
     * Handle contact sharing - link master's telegram_id
     */
    protected function handleContact(array $message)
    {
        $chatId = $message['chat']['id'];
        $contact = $message['contact'];
        $phone = $contact['phone_number'];
        $telegramId = (string) $message['from']['id'];
        $username = $message['from']['username'] ?? null;

        // Normalize phone
        $normalized = $this->normalizePhone($phone);

        Log::info('TherapistBot contact received', [
            'chat_id' => $chatId,
            'phone' => $normalized,
            'telegram_id' => $telegramId,
        ]);

        // Find master by phone
        $master = Master::where('phone', $normalized)
            ->orWhere('phone', '+' . ltrim($normalized, '+'))
            ->orWhere('phone', ltrim($normalized, '+'))
            ->orWhere('phone', $phone)
            ->first();

        if (!$master) {
            $keyboard = ['remove_keyboard' => true];
            $this->bot->sendMessage($chatId,
                "❌ Bu telefon raqami tizimda topilmadi.\n\n"
                . "📞 *{$normalized}*\n\n"
                . "Iltimos, admin bilan bog'laning.",
                'Markdown',
                $keyboard
            );
            return response()->json(['ok' => true]);
        }

        // Update master's telegram info
        $master->update([
            'telegram_id' => $telegramId,
            'telegram_username' => $username,
        ]);

        $keyboard = ['remove_keyboard' => true];

        $this->bot->sendMessage($chatId,
            "✅ *Muvaffaqiyatli ulandi!*\n\n"
            . "👤 {$master->full_name}\n"
            . "📱 {$master->phone}\n\n"
            . "Endi siz buyurtmalar haqida xabarlarni shu botda olasiz.\n\n"
            . "📲 *Buyruqlar:*\n"
            . "/orders - Bugungi buyurtmalar\n"
            . "/status - Hisobingiz holati\n"
            . "/help - Yordam",
            'Markdown',
            $keyboard
        );

        return response()->json(['ok' => true]);
    }

    /**
     * Handle /status command
     */
    protected function handleStatus(array $message)
    {
        $chatId = $message['chat']['id'];
        $telegramId = (string) $message['from']['id'];

        $master = Master::where('telegram_id', $telegramId)->first();

        if (!$master) {
            $this->bot->sendMessage($chatId,
                "❌ Hisobingiz ulanmagan.\n\n/start buyrug'ini yuboring."
            );
            return response()->json(['ok' => true]);
        }

        $todayOrders = Order::where('master_id', $master->id)
            ->where('booking_date', today())
            ->whereNotIn('status', [Order::STATUS_CANCELLED])
            ->count();

        $this->bot->sendMessage($chatId,
            "✅ *Ulangan*\n\n"
            . "👤 {$master->full_name}\n"
            . "📱 {$master->phone}\n\n"
            . "📅 Bugun: *{$todayOrders}* ta buyurtma\n"
            . "📲 Telegram xabar: " . ($master->notify_telegram ? '✅' : '❌') . "\n"
            . "📱 SMS xabar: " . ($master->notify_sms ? '✅' : '❌')
        );

        return response()->json(['ok' => true]);
    }

    /**
     * Handle /orders command
     */
    protected function handleTodayOrders(array $message)
    {
        $chatId = $message['chat']['id'];
        $telegramId = (string) $message['from']['id'];

        $master = Master::where('telegram_id', $telegramId)->first();

        if (!$master) {
            $this->bot->sendMessage($chatId,
                "❌ Hisobingiz ulanmagan.\n\n/start buyrug'ini yuboring."
            );
            return response()->json(['ok' => true]);
        }

        $this->bot->sendTodayOrders($master);

        return response()->json(['ok' => true]);
    }

    /**
     * Handle /help command
     */
    protected function handleHelp(array $message)
    {
        $chatId = $message['chat']['id'];

        $this->bot->sendMessage($chatId,
            "📖 *Yordam*\n\n"
            . "*Buyruqlar:*\n"
            . "/start - Ro'yxatdan o'tish\n"
            . "/orders - Bugungi buyurtmalar\n"
            . "/status - Hisobingiz holati\n"
            . "/help - Shu yordam\n\n"
            . "*Xabar turlari:*\n"
            . "🆕 Yangi buyurtma - tayinlanganda\n"
            . "✅ TAYYOR - yo'lga chiqish vaqti\n"
            . "❌ Bekor - buyurtma bekor qilinganda\n\n"
            . "*Muammo bo'lsa:*\n"
            . "Admin bilan bog'laning: @ssaidabbos"
        );

        return response()->json(['ok' => true]);
    }

    /**
     * Handle "Boshladim" callback
     */
    protected function handleStartSession($orderId, $chatId, $messageId, $userId)
    {
        $order = Order::find($orderId);

        if (!$order || $order->status !== Order::STATUS_CONFIRMED) {
            $this->bot->sendMessage($chatId, "⚠️ Bu buyurtmani boshlab bo'lmaydi.");
            return response()->json(['ok' => true]);
        }

        // Update order status
        $order->update(['status' => Order::STATUS_IN_PROGRESS]);

        // Update the message
        $text = "🔄 *SEANS BOSHLANDI*\n\n"
            . "📋 #{$order->order_number}\n"
            . "⏱ Boshlanish vaqti: " . now()->format('H:i') . "\n\n"
            . "Omad! 💪";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '✔️ Tugatdim', 'callback_data' => "complete:{$order->id}"],
                ],
            ],
        ];

        $this->bot->editMessage($chatId, $messageId, $text, $keyboard);

        return response()->json(['ok' => true]);
    }

    /**
     * Handle "Tugatdim" callback
     */
    protected function handleCompleteSession($orderId, $chatId, $messageId, $userId)
    {
        $order = Order::find($orderId);

        if (!$order || $order->status !== Order::STATUS_IN_PROGRESS) {
            $this->bot->sendMessage($chatId, "⚠️ Bu buyurtmani tugatib bo'lmaydi.");
            return response()->json(['ok' => true]);
        }

        // Update order status
        $order->update([
            'status' => Order::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        // Update the message
        $text = "✔️ *SEANS TUGADI*\n\n"
            . "📋 #{$order->order_number}\n"
            . "⏱ Tugash vaqti: " . now()->format('H:i') . "\n\n"
            . "Rahmat! Yaxshi ish! 👏";

        $this->bot->editMessage($chatId, $messageId, $text);

        return response()->json(['ok' => true]);
    }

    /**
     * Normalize phone number
     */
    protected function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        if (str_starts_with($digits, '998')) {
            return '+' . $digits;
        }

        if (strlen($digits) === 9) {
            return '+998' . $digits;
        }

        return '+' . $digits;
    }
}
