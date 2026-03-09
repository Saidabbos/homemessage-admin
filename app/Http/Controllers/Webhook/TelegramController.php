<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Rating;
use App\Models\Master;
use App\Models\User;
use App\Services\TelegramBotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TelegramController extends Controller
{
    public function __construct(
        protected TelegramBotService $telegram
    ) {}

    /**
     * Handle incoming webhook from Telegram
     */
    public function webhook(Request $request)
    {
        $update = $request->all();
        
        Log::info('Telegram webhook received', ['update' => $update]);

        // Handle callback queries (button clicks)
        if (isset($update['callback_query'])) {
            return $this->handleCallbackQuery($update['callback_query']);
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
    protected function handleCallbackQuery(array $callback)
    {
        $data = $callback['data'] ?? '';
        $chatId = $callback['message']['chat']['id'] ?? null;
        $messageId = $callback['message']['message_id'] ?? null;
        $userId = $callback['from']['id'] ?? null;

        // Parse callback data: action:param1:param2
        $parts = explode(':', $data);
        $action = $parts[0] ?? '';

        Log::info('Telegram callback', ['action' => $action, 'data' => $data, 'user' => $userId]);

        switch ($action) {
            case 'rate':
                // rate:order_id:type:rating
                return $this->handleRating($parts, $chatId, $messageId, $userId);
            
            case 'rate_start':
                // rate_start:order_id:type
                return $this->showRatingKeyboard($parts, $chatId, $messageId);
            
            case 'end_session':
                // end_session:order_id:reporter (client/master)
                return $this->handleEndSession($parts, $chatId, $messageId, $userId);
            
            case 'confirm_end':
                // confirm_end:order_id:reporter
                return $this->showEndSessionRating($parts, $chatId, $messageId);
            
            default:
                // Answer callback to remove loading state
                $this->telegram->answerCallback($callback['id'], 'Noma\'lum buyruq');
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Show rating keyboard (1-5 stars)
     */
    protected function showRatingKeyboard(array $parts, $chatId, $messageId)
    {
        $orderId = $parts[1] ?? null;
        $type = $parts[2] ?? 'client_to_master';

        $order = Order::find($orderId);
        if (!$order) {
            $this->telegram->sendMessage($chatId, '❌ Buyurtma topilmadi');
            return response()->json(['ok' => true]);
        }

        $text = $type === 'client_to_master' 
            ? "⭐ Masterni baholang:\n\n{$order->master->full_name}"
            : "⭐ Mijozni baholang:\n\n{$order->customer->name}";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '1 ⭐', 'callback_data' => "rate:{$orderId}:{$type}:1"],
                    ['text' => '2 ⭐', 'callback_data' => "rate:{$orderId}:{$type}:2"],
                    ['text' => '3 ⭐', 'callback_data' => "rate:{$orderId}:{$type}:3"],
                    ['text' => '4 ⭐', 'callback_data' => "rate:{$orderId}:{$type}:4"],
                    ['text' => '5 ⭐', 'callback_data' => "rate:{$orderId}:{$type}:5"],
                ],
            ],
        ];

        $this->telegram->editMessage($chatId, $messageId, $text, $keyboard);

        return response()->json(['ok' => true]);
    }

    /**
     * Handle rating submission
     */
    protected function handleRating(array $parts, $chatId, $messageId, $userId)
    {
        $orderId = $parts[1] ?? null;
        $type = $parts[2] ?? 'client_to_master';
        $rating = (int) ($parts[3] ?? 0);

        if ($rating < 1 || $rating > 5) {
            return response()->json(['ok' => true]);
        }

        $order = Order::with(['master', 'customer'])->find($orderId);
        if (!$order) {
            $this->telegram->sendMessage($chatId, '❌ Buyurtma topilmadi');
            return response()->json(['ok' => true]);
        }

        // Create or update rating
        $ratingRecord = Rating::updateOrCreate(
            ['order_id' => $orderId, 'type' => $type],
            [
                'master_id' => $order->master_id,
                'customer_id' => $order->customer_id,
                'overall_rating' => $rating,
                'rated_at' => Carbon::now(),
            ]
        );

        // Update cached ratings
        if ($type === 'client_to_master') {
            $avg = Rating::getMasterAverage($order->master_id);
            $count = Rating::getMasterRatingCount($order->master_id);
            Master::where('id', $order->master_id)->update([
                'rating' => $avg,
                'rating_count' => $count,
            ]);
            $targetName = $order->master->full_name;
        } else {
            $avg = Rating::getCustomerAverage($order->customer_id);
            $count = Rating::getCustomerRatingCount($order->customer_id);
            User::where('id', $order->customer_id)->update([
                'rating' => $avg,
                'rating_count' => $count,
            ]);
            $targetName = $order->customer->name ?? 'Mijoz';
        }

        $stars = str_repeat('⭐', $rating);
        $text = "✅ Rahmat! Siz {$targetName}ga {$stars} baho berdingiz.";

        $this->telegram->editMessage($chatId, $messageId, $text);

        return response()->json(['ok' => true]);
    }

    /**
     * Handle end session request (problem reported)
     */
    protected function handleEndSession(array $parts, $chatId, $messageId, $userId)
    {
        $orderId = $parts[1] ?? null;
        $reporter = $parts[2] ?? 'client'; // client or master

        $order = Order::find($orderId);
        if (!$order) {
            $this->telegram->sendMessage($chatId, '❌ Buyurtma topilmadi');
            return response()->json(['ok' => true]);
        }

        $text = "⚠️ Seansni tugatishni tasdiqlaysizmi?\n\n";
        $text .= "Sabab va baho so'raladi.";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '✅ Ha, tugatish', 'callback_data' => "confirm_end:{$orderId}:{$reporter}"],
                    ['text' => '❌ Bekor qilish', 'callback_data' => "cancel_end:{$orderId}"],
                ],
            ],
        ];

        $this->telegram->editMessage($chatId, $messageId, $text, $keyboard);

        return response()->json(['ok' => true]);
    }

    /**
     * Show rating options after confirming session end
     */
    protected function showEndSessionRating(array $parts, $chatId, $messageId)
    {
        $orderId = $parts[1] ?? null;
        $reporter = $parts[2] ?? 'client';

        $type = $reporter === 'client' ? 'client_to_master' : 'master_to_client';
        
        $text = "Iltimos, baholang:";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '1 ⭐', 'callback_data' => "rate:{$orderId}:{$type}:1"],
                    ['text' => '2 ⭐', 'callback_data' => "rate:{$orderId}:{$type}:2"],
                    ['text' => '3 ⭐', 'callback_data' => "rate:{$orderId}:{$type}:3"],
                ],
                [
                    ['text' => '4 ⭐', 'callback_data' => "rate:{$orderId}:{$type}:4"],
                    ['text' => '5 ⭐', 'callback_data' => "rate:{$orderId}:{$type}:5"],
                ],
            ],
        ];

        // Also update order status to completed/cancelled
        Order::where('id', $orderId)->update([
            'status' => Order::STATUS_COMPLETED,
        ]);

        $this->telegram->editMessage($chatId, $messageId, $text, $keyboard);

        return response()->json(['ok' => true]);
    }

    /**
     * Handle regular message
     */
    protected function handleMessage(array $message)
    {
        $chatId = $message['chat']['id'] ?? null;
        $text = $message['text'] ?? '';

        // Handle contact sharing (for master registration)
        if (isset($message['contact'])) {
            return $this->handleContact($message);
        }

        // Handle /start command
        if (str_starts_with($text, '/start')) {
            return $this->handleStart($message);
        }

        // Handle /status command
        if (str_starts_with($text, '/status')) {
            return $this->handleStatus($message);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Handle /start command - show registration button for masters
     */
    protected function handleStart(array $message)
    {
        $chatId = $message['chat']['id'];
        $firstName = $message['from']['first_name'] ?? 'Salom';
        $telegramId = $message['from']['id'];

        // Check if already registered as master
        $master = Master::where('telegram_id', $telegramId)->first();

        if ($master) {
            $this->telegram->sendMessage($chatId, 
                "👋 Salom, {$master->full_name}!\n\n" .
                "✅ Siz allaqachon ro'yxatdan o'tgansiz.\n\n" .
                "📲 *Bildirishnomalar:*\n" .
                "• Telegram: " . ($master->notify_telegram ? '✅ Yoqilgan' : '❌ O\'chirilgan') . "\n" .
                "• SMS: " . ($master->notify_sms ? '✅ Yoqilgan' : '❌ O\'chirilgan') . "\n\n" .
                "Yangi buyurtmalar haqida xabar olasiz.",
                'Markdown'
            );
            return response()->json(['ok' => true]);
        }

        $text = "👋 Salom, {$firstName}!\n\n"
            . "*Sabai* massajchilar uchun xabar boti.\n\n"
            . "🔗 *Hisobingizni ulash uchun* quyidagi tugmani bosib telefon raqamingizni yuboring.\n\n"
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

        $this->telegram->sendMessageWithKeyboard($chatId, $text, $keyboard);

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
        $telegramUserId = $message['from']['id'];
        $username = $message['from']['username'] ?? null;

        // Normalize phone number
        $normalizedPhone = $this->normalizePhone($phone);

        Log::info('Master contact received', [
            'chat_id' => $chatId,
            'phone' => $normalizedPhone,
            'telegram_id' => $telegramUserId,
        ]);

        // Find master by phone (try multiple formats)
        $master = Master::where('phone', $normalizedPhone)
            ->orWhere('phone', '+' . ltrim($normalizedPhone, '+'))
            ->orWhere('phone', ltrim($normalizedPhone, '+'))
            ->orWhere('phone', $phone)
            ->first();

        if (!$master) {
            $this->telegram->sendMessage($chatId, 
                "❌ Bu telefon raqami tizimda topilmadi.\n\n"
                . "📞 *{$normalizedPhone}*\n\n"
                . "Iltimos, admin bilan bog'laning yoki to'g'ri raqamni tekshiring.",
                'Markdown'
            );
            return response()->json(['ok' => true]);
        }

        // Update master's telegram info
        $master->update([
            'telegram_id' => (string) $telegramUserId,
            'telegram_username' => $username,
        ]);

        $keyboard = ['remove_keyboard' => true];

        $this->telegram->sendMessageWithKeyboard($chatId, 
            "✅ *Muvaffaqiyatli ulandi!*\n\n"
            . "👤 {$master->full_name}\n"
            . "📱 {$master->phone}\n\n"
            . "Endi siz buyurtmalar haqida xabarlarni shu botda olasiz.\n\n"
            . "📲 *Xabar turlari:*\n"
            . "• 🆕 Yangi buyurtma tayinlanganda\n"
            . "• ✅ Yo'lga chiqish vaqti kelganda\n"
            . "• ❌ Buyurtma bekor qilinganda",
            $keyboard
        );

        return response()->json(['ok' => true]);
    }

    /**
     * Handle /status command - show connection status
     */
    protected function handleStatus(array $message)
    {
        $chatId = $message['chat']['id'];
        $telegramId = $message['from']['id'];

        $master = Master::where('telegram_id', $telegramId)->first();

        if ($master) {
            $this->telegram->sendMessage($chatId, 
                "✅ *Ulangan*\n\n"
                . "👤 {$master->full_name}\n"
                . "📲 Telegram: " . ($master->notify_telegram ? '✅' : '❌') . "\n"
                . "📱 SMS: " . ($master->notify_sms ? '✅' : '❌'),
                'Markdown'
            );
        } else {
            $this->telegram->sendMessage($chatId, 
                "❌ Hisobingiz ulanmagan.\n\n/start buyrug'ini yuboring."
            );
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Normalize phone number to standard format
     */
    protected function normalizePhone(string $phone): string
    {
        // Remove all non-digit characters
        $digits = preg_replace('/\D/', '', $phone);

        // If starts with 998, return with +
        if (str_starts_with($digits, '998')) {
            return '+' . $digits;
        }

        // If 9 digits (local format), add +998
        if (strlen($digits) === 9) {
            return '+998' . $digits;
        }

        return '+' . $digits;
    }
}
