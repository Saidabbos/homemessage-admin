<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    /**
     * Check if payments are enabled
     */
    public function isEnabled(): bool
    {
        return config('services.payment.enabled', false);
    }

    /**
     * Get available payment providers
     */
    public function getAvailableProviders(): array
    {
        $providers = [];

        if (config('services.payme.enabled')) {
            $providers[] = [
                'id' => Payment::PROVIDER_PAYME,
                'name' => 'Payme',
                'icon' => '/images/payment/payme.svg',
            ];
        }

        if (config('services.click.enabled')) {
            $providers[] = [
                'id' => Payment::PROVIDER_CLICK,
                'name' => 'Click',
                'icon' => '/images/payment/click.svg',
            ];
        }

        return $providers;
    }

    /**
     * Create payment for order
     */
    public function createPayment(Order $order, string $provider): Payment
    {
        // Check if order already has pending payment
        $existingPayment = $order->payments()
            ->where('status', Payment::STATUS_PENDING)
            ->where('provider', $provider)
            ->first();

        if ($existingPayment) {
            return $existingPayment;
        }

        $payment = Payment::create([
            'order_id' => $order->id,
            'provider' => $provider,
            'amount' => $order->total_amount,
            'currency' => 'UZS',
            'status' => Payment::STATUS_PENDING,
            'expires_at' => now()->addHours(12),
        ]);

        // Generate payment URL
        $payment->payment_url = $this->generatePaymentUrl($payment);
        $payment->save();

        Log::info('Payment created', [
            'payment_id' => $payment->id,
            'order_id' => $order->id,
            'provider' => $provider,
            'amount' => $payment->amount,
        ]);

        return $payment;
    }

    /**
     * Generate payment URL for provider
     */
    public function generatePaymentUrl(Payment $payment): string
    {
        return match ($payment->provider) {
            Payment::PROVIDER_PAYME => $this->generatePaymeUrl($payment),
            Payment::PROVIDER_CLICK => $this->generateClickUrl($payment),
            default => '',
        };
    }

    /**
     * Generate Payme checkout URL
     */
    protected function generatePaymeUrl(Payment $payment): string
    {
        $merchantId = config('services.payme.merchant_id');
        $checkoutUrl = config('services.payme.checkout_url');

        // Amount in tiyin (1 UZS = 100 tiyin)
        $amountTiyin = (int) ($payment->amount * 100);

        // Encode account data
        $account = base64_encode("m={$merchantId};ac.order_id={$payment->order_id};a={$amountTiyin}");

        return "{$checkoutUrl}/{$account}";
    }

    /**
     * Generate Click payment URL
     */
    protected function generateClickUrl(Payment $payment): string
    {
        $merchantId = config('services.click.merchant_id');
        $serviceId = config('services.click.service_id');

        $params = http_build_query([
            'service_id' => $serviceId,
            'merchant_id' => $merchantId,
            'amount' => $payment->amount,
            'transaction_param' => $payment->order_id,
            'return_url' => route('miniapp.booking.success', ['order_number' => $payment->order->order_number]),
        ]);

        return "https://my.click.uz/services/pay?{$params}";
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus(Payment $payment): array
    {
        return [
            'id' => $payment->id,
            'transaction_id' => $payment->transaction_id,
            'provider' => $payment->provider,
            'status' => $payment->status,
            'status_label' => $payment->status_label,
            'amount' => $payment->amount,
            'formatted_amount' => $payment->formatted_amount,
            'payment_url' => $payment->payment_url,
            'paid_at' => $payment->paid_at?->toIso8601String(),
            'expires_at' => $payment->expires_at?->toIso8601String(),
        ];
    }

    /**
     * Cancel pending payment
     */
    public function cancelPayment(Payment $payment, ?string $reason = null): bool
    {
        if (!$payment->canBeCancelled()) {
            return false;
        }

        $payment->markAsCancelled($reason);

        Log::info('Payment cancelled', [
            'payment_id' => $payment->id,
            'reason' => $reason,
        ]);

        return true;
    }

    /**
     * Generate direct payment link for order (without creating Payment record)
     * Used by admin to send payment link to customer
     */
    public function generateDirectPaymentLink(Order $order, string $provider): ?string
    {
        if (!in_array($provider, [Payment::PROVIDER_PAYME, Payment::PROVIDER_CLICK])) {
            return null;
        }

        return match ($provider) {
            Payment::PROVIDER_PAYME => $this->generateDirectPaymeLink($order),
            Payment::PROVIDER_CLICK => $this->generateDirectClickLink($order),
            default => null,
        };
    }

    /**
     * Generate direct Payme link for order
     */
    protected function generateDirectPaymeLink(Order $order): string
    {
        $merchantId = config('services.payme.merchant_id');
        $checkoutUrl = config('services.payme.checkout_url', 'https://checkout.paycom.uz');

        // Amount in tiyin (1 UZS = 100 tiyin)
        $amountTiyin = (int) ($order->total_amount * 100);

        // Encode account data
        $account = base64_encode("m={$merchantId};ac.order_id={$order->id};a={$amountTiyin}");

        return "{$checkoutUrl}/{$account}";
    }

    /**
     * Generate direct Click link for order
     */
    protected function generateDirectClickLink(Order $order): string
    {
        $merchantId = config('services.click.merchant_id');
        $serviceId = config('services.click.service_id');

        $params = http_build_query([
            'service_id' => $serviceId,
            'merchant_id' => $merchantId,
            'amount' => $order->total_amount,
            'transaction_param' => $order->id,
        ]);

        return "https://my.click.uz/services/pay?{$params}";
    }

    /**
     * Get payment providers config for admin
     */
    public function getProvidersConfig(): array
    {
        return [
            'payme' => [
                'enabled' => config('services.payme.enabled', false),
                'configured' => !empty(config('services.payme.merchant_id')),
                'name' => 'Payme',
            ],
            'click' => [
                'enabled' => config('services.click.enabled', false),
                'configured' => !empty(config('services.click.merchant_id')),
                'name' => 'Click',
            ],
        ];
    }
}
