<?php

namespace App\Services;

use App\Models\Master;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MasterNotificationService
{
    protected SmsService $smsService;
    protected TherapistBotService $therapistBot;

    public function __construct(SmsService $smsService, TherapistBotService $therapistBot)
    {
        $this->smsService = $smsService;
        $this->therapistBot = $therapistBot;
    }

    /**
     * Notify master about assigned order
     */
    public function notifyAssigned(Order $order): array
    {
        $master = $order->master;
        if (!$master) {
            return ['telegram' => false, 'sms' => false, 'error' => 'No master assigned'];
        }

        $results = ['telegram' => false, 'sms' => false];

        // Telegram notification via Therapist Bot
        if ($master->notify_telegram && $master->hasTelegram()) {
            $results['telegram'] = $this->therapistBot->notifyAssigned($order);
        }

        // SMS notification
        if ($master->notify_sms && $master->phone) {
            $smsText = $this->buildAssignedSms($order);
            $results['sms'] = $this->sendSms($master->phone, $smsText);
        }

        Log::info('Master notification sent (ASSIGNED)', [
            'order_id' => $order->id,
            'master_id' => $master->id,
            'results' => $results,
        ]);

        return $results;
    }

    /**
     * Notify master when order is READY (go to client)
     */
    public function notifyReady(Order $order): array
    {
        $master = $order->master;
        if (!$master) {
            return ['telegram' => false, 'sms' => false, 'error' => 'No master assigned'];
        }

        $results = ['telegram' => false, 'sms' => false];

        // Telegram notification via Therapist Bot
        if ($master->notify_telegram && $master->hasTelegram()) {
            $results['telegram'] = $this->therapistBot->notifyReady($order);
        }

        // SMS notification (shorter)
        if ($master->notify_sms && $master->phone) {
            $smsText = $this->buildReadySms($order);
            $results['sms'] = $this->sendSms($master->phone, $smsText);
        }

        Log::info('Master notification sent (READY)', [
            'order_id' => $order->id,
            'master_id' => $master->id,
            'results' => $results,
        ]);

        return $results;
    }

    /**
     * Notify master about order cancellation
     */
    public function notifyCancelled(Order $order): array
    {
        $master = $order->master;
        if (!$master) {
            return ['telegram' => false, 'sms' => false];
        }

        $results = ['telegram' => false, 'sms' => false];

        if ($master->notify_telegram && $master->hasTelegram()) {
            $results['telegram'] = $this->therapistBot->notifyCancelled($order);
        }

        if ($master->notify_sms && $master->phone) {
            $smsText = $this->buildCancelledSms($order);
            $results['sms'] = $this->sendSms($master->phone, $smsText);
        }

        return $results;
    }

    /**
     * Send SMS via Eskiz
     */
    protected function sendSms(string $phone, string $message): bool
    {
        // Reuse SMS service auth mechanism
        try {
            $token = $this->getSmsToken();
            if (!$token) {
                return false;
            }

            $response = Http::withToken($token)
                ->post(config('services.eskiz.base_url') . '/message/sms/send', [
                    'mobile_phone' => ltrim($phone, '+'),
                    'message' => $message,
                    'from' => config('services.eskiz.sender'),
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('SMS send failed', ['phone' => $phone, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get SMS auth token (cached)
     */
    protected function getSmsToken(): ?string
    {
        $cacheKey = config('services.eskiz.token_cache_key');

        return cache()->remember($cacheKey, config('services.eskiz.token_ttl'), function () {
            $response = Http::post(config('services.eskiz.base_url') . '/auth/login', [
                'email' => config('services.eskiz.email'),
                'password' => config('services.eskiz.password'),
            ]);

            return $response->successful() ? $response->json('data.token') : null;
        });
    }

    // ==================== SMS Message Builders ====================

    protected function buildAssignedSms(Order $order): string
    {
        $date = $order->booking_date?->format('d.m') ?? '-';
        $time = substr($order->arrival_window_start ?? '', 0, 5);

        return "HM: Yangi buyurtma #{$order->order_number}. {$date} {$time}. Tafsilotlar Telegramda.";
    }

    protected function buildReadySms(Order $order): string
    {
        $time = substr($order->arrival_window_start ?? '', 0, 5);
        $address = $order->address ? mb_substr($order->address, 0, 50) : '';

        return "HM: #{$order->order_number} TAYYOR! {$time}. {$address}. Tafsilotlar Telegramda.";
    }

    protected function buildCancelledSms(Order $order): string
    {
        return "HM: #{$order->order_number} bekor qilindi.";
    }
}
