---
name: payment-integration
description: Guide for integrating Payme and Click payment providers with webhooks
---

# Payment Integration Skill

This skill covers implementing Payme and Click payment integrations for Golden Touch.

## When to Use This Skill

- Setting up payment providers
- Creating invoice/checkout flows
- Implementing webhook handlers
- Handling payment confirmations

---

## Payment Flow Overview

```
1. Dispatcher creates invoice in admin
2. System calls Payme/Click API to create payment
3. System gets payment URL and reference
4. SMS sent to client with payment link
5. Client pays via Payme/Click
6. Webhook notifies system of payment
7. System updates order: PAID status
8. Telegram notification sent
```

---

## Configuration

### Environment Variables

```env
# Payme
PAYME_MERCHANT_ID=your_merchant_id
PAYME_SECRET_KEY=your_secret_key
PAYME_TEST_MODE=true
PAYME_CHECKOUT_URL=https://checkout.paycom.uz

# Click
CLICK_MERCHANT_ID=your_merchant_id
CLICK_SERVICE_ID=your_service_id
CLICK_SECRET_KEY=your_secret_key
CLICK_TEST_MODE=true
```

### Config File

```php
// config/payments.php

return [
    'payme' => [
        'merchant_id' => env('PAYME_MERCHANT_ID'),
        'secret_key' => env('PAYME_SECRET_KEY'),
        'test_mode' => env('PAYME_TEST_MODE', true),
        'checkout_url' => env('PAYME_CHECKOUT_URL', 'https://checkout.paycom.uz'),
    ],

    'click' => [
        'merchant_id' => env('CLICK_MERCHANT_ID'),
        'service_id' => env('CLICK_SERVICE_ID'),
        'secret_key' => env('CLICK_SECRET_KEY'),
        'test_mode' => env('CLICK_TEST_MODE', true),
    ],

    'webhook_ips' => [
        'payme' => ['185.8.212.0/24', '195.158.31.0/24'],
        'click' => ['185.8.213.0/24'],
    ],
];
```

---

## Payment Service

```php
// app/Services/PaymentService.php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    /**
     * Create invoice for order
     */
    public function createInvoice(Order $order, string $provider, int $amount = null): array
    {
        $amount = $amount ?? $order->total_amount;

        return match($provider) {
            'payme' => $this->createPaymeInvoice($order, $amount),
            'click' => $this->createClickInvoice($order, $amount),
            default => throw new \InvalidArgumentException("Unknown provider: {$provider}"),
        };
    }

    /**
     * Create Payme checkout link
     */
    private function createPaymeInvoice(Order $order, int $amount): array
    {
        $merchantId = config('payments.payme.merchant_id');
        $amountTiyin = $amount * 100; // Convert to tiyin (1 UZS = 100 tiyin)

        // Build checkout URL with base64 encoded params
        $params = base64_encode(sprintf(
            'm=%s;ac.order_id=%d;a=%d;c=%s',
            $merchantId,
            $order->id,
            $amountTiyin,
            route('api.payments.webhook', 'payme')
        ));

        $checkoutUrl = config('payments.payme.checkout_url') . '/' . $params;

        // Generate reference for tracking
        $reference = 'PM' . $order->id . '_' . time();

        $this->logPayment($order, 'payme', 'invoice_created', [
            'amount' => $amount,
            'reference' => $reference,
        ]);

        return [
            'url' => $checkoutUrl,
            'reference' => $reference,
            'expires_at' => now()->addMinutes(30),
        ];
    }

    /**
     * Create Click checkout link
     */
    private function createClickInvoice(Order $order, int $amount): array
    {
        $merchantId = config('payments.click.merchant_id');
        $serviceId = config('payments.click.service_id');

        // Build Click checkout URL
        $params = http_build_query([
            'merchant_id' => $merchantId,
            'service_id' => $serviceId,
            'amount' => $amount,
            'transaction_param' => $order->id,
            'return_url' => route('cabinet.orders.show', $order->public_token),
        ]);

        $checkoutUrl = 'https://my.click.uz/services/pay?' . $params;

        $reference = 'CL' . $order->id . '_' . time();

        $this->logPayment($order, 'click', 'invoice_created', [
            'amount' => $amount,
            'reference' => $reference,
        ]);

        return [
            'url' => $checkoutUrl,
            'reference' => $reference,
            'expires_at' => now()->addMinutes(30),
        ];
    }

    /**
     * Handle webhook from payment provider
     */
    public function handleWebhook(string $provider, array $payload, string $signature = null): array
    {
        // Log incoming webhook
        $this->logWebhook($provider, $payload);

        // Verify signature
        if (!$this->verifySignature($provider, $payload, $signature)) {
            Log::warning("Payment webhook signature verification failed", [
                'provider' => $provider,
                'payload' => $payload,
            ]);
            return ['error' => 'Invalid signature'];
        }

        return match($provider) {
            'payme' => $this->handlePaymeWebhook($payload),
            'click' => $this->handleClickWebhook($payload),
            default => ['error' => 'Unknown provider'],
        };
    }

    /**
     * Handle Payme webhook
     */
    private function handlePaymeWebhook(array $payload): array
    {
        $method = $payload['method'] ?? null;

        return match($method) {
            'CheckPerformTransaction' => $this->paymeCheckPerform($payload),
            'CreateTransaction' => $this->paymeCreateTransaction($payload),
            'PerformTransaction' => $this->paymePerformTransaction($payload),
            'CancelTransaction' => $this->paymeCancelTransaction($payload),
            'CheckTransaction' => $this->paymeCheckTransaction($payload),
            default => ['error' => ['code' => -32601, 'message' => 'Method not found']],
        };
    }

    private function paymeCheckPerform(array $payload): array
    {
        $orderId = $payload['params']['account']['order_id'] ?? null;
        $amount = $payload['params']['amount'] ?? 0;

        $order = Order::find($orderId);

        if (!$order) {
            return ['error' => ['code' => -31050, 'message' => 'Order not found']];
        }

        if ($order->payment_status === Order::PAY_PAID) {
            return ['error' => ['code' => -31051, 'message' => 'Already paid']];
        }

        $expectedAmount = $order->total_amount * 100; // tiyin
        if ($amount !== $expectedAmount) {
            return ['error' => ['code' => -31001, 'message' => 'Invalid amount']];
        }

        return ['result' => ['allow' => true]];
    }

    private function paymePerformTransaction(array $payload): array
    {
        $transactionId = $payload['params']['id'] ?? null;
        $orderId = $payload['params']['account']['order_id'] ?? null;

        $order = Order::find($orderId);

        if (!$order) {
            return ['error' => ['code' => -31050, 'message' => 'Order not found']];
        }

        // Check idempotency
        if ($order->payment_status === Order::PAY_PAID &&
            $order->pay_reference === $transactionId) {
            return ['result' => [
                'transaction' => $transactionId,
                'perform_time' => $order->paid_at->timestamp * 1000,
                'state' => 2,
            ]];
        }

        // Confirm payment
        $this->confirmPayment($order, 'payme', [
            'transaction_id' => $transactionId,
            'payload' => $payload,
        ]);

        return ['result' => [
            'transaction' => $transactionId,
            'perform_time' => now()->timestamp * 1000,
            'state' => 2,
        ]];
    }

    /**
     * Handle Click webhook
     */
    private function handleClickWebhook(array $payload): array
    {
        $action = $payload['action'] ?? null;

        if ($action === 0) {
            // Prepare - check if order exists
            return $this->clickPrepare($payload);
        }

        if ($action === 1) {
            // Complete - confirm payment
            return $this->clickComplete($payload);
        }

        return ['error' => -9, 'error_note' => 'Unknown action'];
    }

    private function clickPrepare(array $payload): array
    {
        $orderId = $payload['merchant_trans_id'] ?? null;
        $amount = $payload['amount'] ?? 0;

        $order = Order::find($orderId);

        if (!$order) {
            return ['error' => -5, 'error_note' => 'Order not found'];
        }

        if ($order->payment_status === Order::PAY_PAID) {
            return ['error' => -4, 'error_note' => 'Already paid'];
        }

        if ((int)$amount !== $order->total_amount) {
            return ['error' => -2, 'error_note' => 'Invalid amount'];
        }

        return [
            'error' => 0,
            'error_note' => 'Success',
            'click_trans_id' => $payload['click_trans_id'],
            'merchant_trans_id' => $orderId,
            'merchant_prepare_id' => $orderId,
        ];
    }

    private function clickComplete(array $payload): array
    {
        $orderId = $payload['merchant_trans_id'] ?? null;
        $transactionId = $payload['click_trans_id'] ?? null;
        $error = $payload['error'] ?? 0;

        $order = Order::find($orderId);

        if (!$order) {
            return ['error' => -5, 'error_note' => 'Order not found'];
        }

        // Check for payment error
        if ($error < 0) {
            $this->logPayment($order, 'click', 'payment_failed', $payload);
            $order->update(['payment_status' => Order::PAY_FAILED]);
            return [
                'error' => $error,
                'error_note' => 'Payment failed',
                'click_trans_id' => $transactionId,
                'merchant_trans_id' => $orderId,
            ];
        }

        // Check idempotency
        if ($order->payment_status === Order::PAY_PAID) {
            return [
                'error' => 0,
                'error_note' => 'Success',
                'click_trans_id' => $transactionId,
                'merchant_trans_id' => $orderId,
                'merchant_confirm_id' => $orderId,
            ];
        }

        // Confirm payment
        $this->confirmPayment($order, 'click', [
            'transaction_id' => $transactionId,
            'payload' => $payload,
        ]);

        return [
            'error' => 0,
            'error_note' => 'Success',
            'click_trans_id' => $transactionId,
            'merchant_trans_id' => $orderId,
            'merchant_confirm_id' => $orderId,
        ];
    }

    /**
     * Confirm payment and update order
     */
    private function confirmPayment(Order $order, string $provider, array $data): void
    {
        \DB::transaction(function () use ($order, $provider, $data) {
            $order->update([
                'payment_status' => Order::PAY_PAID,
                'paid_at' => now(),
                'pay_reference' => $data['transaction_id'],
            ]);

            $order->logEvent('paid', [
                'provider' => $provider,
                'transaction_id' => $data['transaction_id'],
            ]);

            $this->logPayment($order, $provider, 'payment_confirmed', $data);

            // Send Telegram notification
            app(TelegramService::class)->sendPaid($order);

            // Auto-reserve if confirmation is complete
            if ($order->confirmation?->call_outcome === 'CONFIRMED' &&
                $order->slot->status === 'PENDING') {
                app(SlotService::class)->markReserved($order->slot, $order);
                $order->update(['status' => Order::STATUS_RESERVED]);

                // Check and send READY notification
                app(OrderService::class)->sendReadyIfEligible($order->fresh());
            }
        });
    }

    /**
     * Verify webhook signature
     */
    private function verifySignature(string $provider, array $payload, ?string $signature): bool
    {
        if (config('payments.' . $provider . '.test_mode')) {
            return true; // Skip verification in test mode
        }

        return match($provider) {
            'payme' => $this->verifyPaymeSignature($payload, $signature),
            'click' => $this->verifyClickSignature($payload),
            default => false,
        };
    }

    private function verifyPaymeSignature(array $payload, ?string $signature): bool
    {
        // Payme uses Basic auth header
        $expectedAuth = base64_encode(
            'Paycom:' . config('payments.payme.secret_key')
        );

        return $signature === $expectedAuth;
    }

    private function verifyClickSignature(array $payload): bool
    {
        $signString = $payload['click_trans_id'] .
                     $payload['service_id'] .
                     config('payments.click.secret_key') .
                     $payload['merchant_trans_id'] .
                     ($payload['merchant_prepare_id'] ?? '') .
                     $payload['amount'] .
                     $payload['action'] .
                     $payload['sign_time'];

        $expectedSign = md5($signString);

        return $payload['sign_string'] === $expectedSign;
    }

    /**
     * Log payment event
     */
    private function logPayment(Order $order, string $provider, string $event, array $data): void
    {
        PaymentLog::create([
            'order_id' => $order->id,
            'provider' => $provider,
            'event' => $event,
            'data' => $data,
        ]);
    }

    /**
     * Log incoming webhook
     */
    private function logWebhook(string $provider, array $payload): void
    {
        Log::channel('payments')->info("Webhook received: {$provider}", [
            'payload' => $payload,
            'ip' => request()->ip(),
        ]);
    }
}
```

---

## Webhook Controller

```php
// app/Http/Controllers/Api/PaymentWebhookController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentWebhookController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    public function handle(Request $request, string $provider)
    {
        // Verify IP whitelist
        if (!$this->isAllowedIp($provider, $request->ip())) {
            abort(403, 'IP not allowed');
        }

        $payload = $request->all();

        // Get signature from header
        $signature = match($provider) {
            'payme' => str_replace('Basic ', '', $request->header('Authorization')),
            'click' => null, // Click uses sign_string in payload
            default => null,
        };

        $result = $this->paymentService->handleWebhook($provider, $payload, $signature);

        return response()->json($result);
    }

    private function isAllowedIp(string $provider, string $ip): bool
    {
        if (config('payments.' . $provider . '.test_mode')) {
            return true;
        }

        $allowedRanges = config('payments.webhook_ips.' . $provider, []);

        foreach ($allowedRanges as $range) {
            if ($this->ipInRange($ip, $range)) {
                return true;
            }
        }

        return false;
    }

    private function ipInRange(string $ip, string $range): bool
    {
        if (strpos($range, '/') === false) {
            return $ip === $range;
        }

        [$subnet, $bits] = explode('/', $range);
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - $bits);

        return ($ip & $mask) === ($subnet & $mask);
    }
}
```

---

## Routes

```php
// routes/api.php

Route::post('/payments/webhook/{provider}', [PaymentWebhookController::class, 'handle'])
    ->name('api.payments.webhook')
    ->withoutMiddleware(['csrf', 'throttle']);
```

---

## PaymentLog Model

```php
// app/Models/PaymentLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentLog extends Model
{
    protected $fillable = [
        'order_id',
        'provider',
        'event',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}

// Migration
Schema::create('payment_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained()->onDelete('cascade');
    $table->string('provider'); // payme, click
    $table->string('event'); // invoice_created, payment_confirmed, etc.
    $table->json('data')->nullable();
    $table->timestamps();

    $table->index(['order_id', 'provider']);
});
```

---

## Admin Invoice UI

```vue
<!-- resources/js/Components/Admin/PaymentBlock.vue -->

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    order: Object,
})

const selectedProvider = ref('payme')
const amount = ref(props.order.total_amount)
const loading = ref(false)

const createInvoice = () => {
    loading.value = true
    router.post(route('admin.orders.invoice', props.order.id), {
        provider: selectedProvider.value,
        amount: amount.value,
    }, {
        onFinish: () => loading.value = false,
    })
}

const copyPaymentLink = () => {
    navigator.clipboard.writeText(props.order.pay_url)
    // Show toast notification
}
</script>

<template>
    <div class="payment-block">
        <h3>Payment</h3>

        <!-- Not Invoiced -->
        <template v-if="order.payment_status === 'NOT_INVOICED'">
            <div class="form-group">
                <label>Amount (UZS)</label>
                <input type="number" v-model="amount" min="1000" step="1000" />
            </div>

            <div class="form-group">
                <label>Provider</label>
                <div class="provider-buttons">
                    <button
                        :class="{ active: selectedProvider === 'payme' }"
                        @click="selectedProvider = 'payme'"
                    >
                        Payme
                    </button>
                    <button
                        :class="{ active: selectedProvider === 'click' }"
                        @click="selectedProvider = 'click'"
                    >
                        Click
                    </button>
                </div>
            </div>

            <button
                @click="createInvoice"
                :disabled="loading"
                class="btn-primary"
            >
                {{ loading ? 'Creating...' : 'Create Invoice' }}
            </button>
        </template>

        <!-- Invoiced -->
        <template v-else-if="order.payment_status === 'INVOICED'">
            <div class="status-badge warning">Waiting for payment</div>

            <div class="payment-info">
                <p><strong>Provider:</strong> {{ order.pay_provider }}</p>
                <p><strong>Amount:</strong> {{ order.formatted_amount }}</p>
                <p v-if="order.pay_expires_at">
                    <strong>Expires:</strong> {{ order.pay_expires_at }}
                </p>
            </div>

            <div class="payment-link">
                <input :value="order.pay_url" readonly />
                <button @click="copyPaymentLink">Copy</button>
            </div>

            <button class="btn-secondary" disabled>
                Waiting for webhook...
            </button>
        </template>

        <!-- Paid -->
        <template v-else-if="order.payment_status === 'PAID'">
            <div class="status-badge success">Payment Confirmed</div>

            <div class="payment-info">
                <p><strong>Provider:</strong> {{ order.pay_provider }}</p>
                <p><strong>Amount:</strong> {{ order.formatted_amount }}</p>
                <p><strong>Paid at:</strong> {{ order.paid_at }}</p>
                <p><strong>Reference:</strong> {{ order.pay_reference }}</p>
            </div>
        </template>

        <!-- Failed -->
        <template v-else-if="order.payment_status === 'FAILED'">
            <div class="status-badge error">Payment Failed</div>
            <button @click="order.payment_status = 'NOT_INVOICED'">
                Try Again
            </button>
        </template>
    </div>
</template>
```

---

## Testing Webhooks

### Payme Test

```bash
curl -X POST http://localhost/api/payments/webhook/payme \
  -H "Content-Type: application/json" \
  -H "Authorization: Basic cGF5Y29tOnlvdXJfc2VjcmV0X2tleQ==" \
  -d '{
    "method": "PerformTransaction",
    "params": {
      "id": "test_transaction_123",
      "account": {
        "order_id": 1
      },
      "amount": 50000000
    }
  }'
```

### Click Test

```bash
curl -X POST http://localhost/api/payments/webhook/click \
  -H "Content-Type: application/json" \
  -d '{
    "click_trans_id": "123456",
    "service_id": "your_service_id",
    "merchant_trans_id": "1",
    "amount": "500000",
    "action": 1,
    "error": 0,
    "sign_time": "2024-01-01 12:00:00",
    "sign_string": "calculated_md5_hash"
  }'
```

---

## Checklist

- [ ] Config file with Payme/Click credentials
- [ ] PaymentService with createInvoice method
- [ ] Payme checkout URL generation
- [ ] Click checkout URL generation
- [ ] Webhook controller with IP verification
- [ ] Payme webhook handler (CheckPerform, Perform, Cancel)
- [ ] Click webhook handler (Prepare, Complete)
- [ ] Signature verification
- [ ] PaymentLog model for audit trail
- [ ] Idempotency handling (prevent duplicate processing)
- [ ] Auto-reserve slot on successful payment
- [ ] Telegram notification on payment
- [ ] Admin UI for invoice creation
- [ ] Test mode support
