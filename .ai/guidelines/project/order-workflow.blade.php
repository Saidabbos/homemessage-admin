## Golden Touch - Order Processing Workflow

This document covers the complete order lifecycle from creation to completion.

---

### Order Statuses

| Status | Code | Description | Next Actions |
|--------|------|-------------|--------------|
| New | `NEW` | Just created by client | Call client |
| Confirming | `CONFIRMING` | Dispatcher calling/verifying | Fill form |
| Waiting Payment | `WAITING_PAYMENT` | Invoice sent, awaiting payment | Wait/remind |
| Paid | `PAID` | Payment confirmed via webhook | Confirm booking |
| Reserved | `RESERVED` | Booking confirmed, slot locked | Send work order |
| Completed | `COMPLETED` | Service delivered, QA done | Archive |
| Cancelled | `CANCELLED` | Cancelled by client/dispatcher | - |
| Cancel Requested | `CANCEL_REQUESTED` | Client requested cancel after RESERVED | Dispatcher decides |

### Payment Statuses

| Status | Code | Description |
|--------|------|-------------|
| Not Invoiced | `NOT_INVOICED` | No invoice created yet |
| Invoiced | `INVOICED` | Invoice created, waiting payment |
| Paid | `PAID` | Payment confirmed |
| Failed | `FAILED` | Payment failed/expired |
| Refunded | `REFUNDED` | Refund processed |

---

### Status Flow Diagram

```
                                    CLIENT FLOW
                                         │
                                         ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                              [ NEW ]                                     │
│                    Order created, Slot → PENDING                        │
│                    Telegram: NEW to OPS group                           │
└───────────────────────────────┬─────────────────────────────────────────┘
                                │
                    Dispatcher calls client
                                │
              ┌─────────────────┼─────────────────┐
              │                 │                 │
              ▼                 ▼                 ▼
        [ CANCELLED ]    [ CONFIRMING ]    [ NO ANSWER ]
        Slot → FREE                        Slot → FREE
              │                 │
              │    Fill confirmation form
              │                 │
              │    ┌────────────┴────────────┐
              │    │                         │
              │    ▼                         ▼
              │ Confirmed              Reschedule
              │    │                   (change slot)
              │    │                         │
              │    └──────────┬──────────────┘
              │               │
              │    Create Invoice (Payme/Click)
              │               │
              │               ▼
              │    [ WAITING_PAYMENT ]
              │    Payment status: INVOICED
              │    Send payment link to client
              │               │
              │    ┌──────────┼──────────┐
              │    │          │          │
              │    ▼          ▼          ▼
              │  Timeout    WEBHOOK    Manual
              │  (expire)   PAID      confirm
              │    │          │          │
              │    ▼          └────┬─────┘
              │  FAILED            │
              │    │               ▼
              │    │         [ PAID ]
              │    │    Payment status: PAID
              │    │    Telegram: PAID to OPS
              │    │               │
              │    │    Confirm Booking
              │    │               │
              │    │               ▼
              │    │        [ RESERVED ]
              │    │        Slot → RESERVED
              │    │        Telegram: READY to therapists
              │    │               │
              │    │    ┌──────────┴──────────┐
              │    │    │                     │
              │    │    ▼                     ▼
              │    │  Generate           Client requests
              │    │  Work Order            cancel
              │    │    │                     │
              │    │    │                     ▼
              │    │    │           [ CANCEL_REQUESTED ]
              │    │    │              Dispatcher decides
              │    │    │                     │
              │    │    │    ┌────────────────┼────────────────┐
              │    │    │    │                │                │
              │    │    │    ▼                ▼                ▼
              │    │    │  Approve         Reject          Refund
              │    │    │  (cancel)        (keep)          & cancel
              │    │    │    │                │                │
              │    │    │    ▼                │                │
              │    │    │  CANCELLED         │                │
              │    │    │    │               │                │
              │    │    └────┼───────────────┘                │
              │    │         │                                │
              │    │         ▼                                │
              │    │    Service Delivered                     │
              │    │         │                                │
              │    │    Fill QA Form                          │
              │    │         │                                │
              │    │         ▼                                │
              │    │    [ COMPLETED ]                         │
              │    │    Telegram: Completed (optional)        │
              │    │                                          │
              └────┴──────────────────────────────────────────┘
```

---

### Order Data Structure

```php
// Order Model
class Order extends Model
{
    protected $fillable = [
        'client_id',
        'therapist_id',
        'slot_id',
        'massage_type_id',
        'oil_type_id',
        'public_token',
        'status',
        'payment_status',
        'total_amount',
        'pay_provider',      // payme, click
        'pay_reference',     // transaction ID from provider
        'pay_url',           // checkout URL
        'pay_expires_at',
        'paid_at',
        'work_order_status', // NONE, READY, SENT
        'work_order_text',
        'sent_to_therapist_at',
        'ready_sent_at',
    ];

    // Relationships
    public function client() { return $this->belongsTo(Client::class); }
    public function therapist() { return $this->belongsTo(Therapist::class); }
    public function slot() { return $this->belongsTo(Slot::class); }
    public function massageType() { return $this->belongsTo(MassageType::class); }
    public function oilType() { return $this->belongsTo(OilType::class); }
    public function confirmation() { return $this->hasOne(OrderConfirmation::class); }
    public function quality() { return $this->hasOne(OrderQuality::class); }
    public function auditLogs() { return $this->hasMany(OrderAuditLog::class); }
}
```

---

### Order Service Implementation

```php
// app/Services/OrderService.php

class OrderService
{
    public function __construct(
        private SlotService $slotService,
        private PaymentService $paymentService,
        private TelegramService $telegram,
    ) {}

    /**
     * Create new order from client booking
     */
    public function create(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $slot = Slot::lockForUpdate()->findOrFail($data['slot_id']);

            if (!$slot->canBook()) {
                throw new SlotNotAvailableException();
            }

            // Get or create client
            $client = Client::firstOrCreate(
                ['phone' => $data['phone']],
                ['name' => $data['name'] ?? null]
            );

            $order = Order::create([
                'client_id' => $client->id,
                'therapist_id' => $slot->therapist_id,
                'slot_id' => $slot->id,
                'massage_type_id' => $data['massage_type_id'],
                'oil_type_id' => $data['oil_type_id'] ?? null,
                'total_amount' => $data['total_amount'] ?? config('booking.default_price'),
                'status' => Order::STATUS_NEW,
                'payment_status' => Order::PAY_NOT_INVOICED,
                'public_token' => Str::random(32),
            ]);

            $this->slotService->markPending($slot, $order);
            $this->logEvent($order, 'created');
            $this->telegram->sendNew($order);

            return $order;
        });
    }

    /**
     * Save confirmation form (pre-payment questionnaire)
     */
    public function saveConfirmation(Order $order, array $data): OrderConfirmation
    {
        $confirmation = $order->confirmation()->updateOrCreate(
            ['order_id' => $order->id],
            array_merge($data, [
                'filled_by' => auth()->id(),
                'filled_at' => now(),
            ])
        );

        // Handle call outcome
        if ($data['call_outcome'] === 'CANCELLED' || $data['call_outcome'] === 'NO_ANSWER') {
            $this->cancel($order, $data['call_outcome']);
        } elseif ($data['call_outcome'] === 'CONFIRMED') {
            $order->update(['status' => Order::STATUS_CONFIRMING]);
            $this->logEvent($order, 'confirmed');
        }

        return $confirmation;
    }

    /**
     * Create payment invoice
     */
    public function createInvoice(Order $order, string $provider, int $amount = null): array
    {
        $amount = $amount ?? $order->total_amount;

        $result = $this->paymentService->createInvoice($order, $provider, $amount);

        $order->update([
            'status' => Order::STATUS_WAITING_PAYMENT,
            'payment_status' => Order::PAY_INVOICED,
            'pay_provider' => $provider,
            'pay_reference' => $result['reference'],
            'pay_url' => $result['url'],
            'pay_expires_at' => $result['expires_at'] ?? null,
            'total_amount' => $amount,
        ]);

        $this->logEvent($order, 'invoice_created', [
            'provider' => $provider,
            'amount' => $amount,
        ]);

        return $result;
    }

    /**
     * Handle payment confirmation (from webhook)
     */
    public function confirmPayment(Order $order, array $webhookData): void
    {
        DB::transaction(function () use ($order, $webhookData) {
            $order->update([
                'payment_status' => Order::PAY_PAID,
                'paid_at' => now(),
                'status' => Order::STATUS_PAID,
            ]);

            $this->logEvent($order, 'paid', $webhookData);
            $this->telegram->sendPaid($order);

            // Auto-reserve if confirmation is complete
            if ($order->confirmation?->call_outcome === 'CONFIRMED') {
                $this->confirmBooking($order);
            }
        });
    }

    /**
     * Confirm booking and reserve slot
     */
    public function confirmBooking(Order $order): void
    {
        if ($order->payment_status !== Order::PAY_PAID) {
            throw new PaymentRequiredException();
        }

        DB::transaction(function () use ($order) {
            $this->slotService->markReserved($order->slot, $order);

            $order->update(['status' => Order::STATUS_RESERVED]);
            $this->logEvent($order, 'reserved');

            // Send READY notification if all conditions met
            $this->sendReadyIfEligible($order);
        });
    }

    /**
     * Send READY notification to therapist group
     */
    public function sendReadyIfEligible(Order $order): bool
    {
        // Check conditions
        if ($order->status !== Order::STATUS_RESERVED) return false;
        if ($order->payment_status !== Order::PAY_PAID) return false;
        if (!$order->confirmation) return false;
        if ($order->confirmation->call_outcome !== 'CONFIRMED') return false;
        if ($order->ready_sent_at) return false; // Already sent

        // Send notification
        $this->telegram->sendReady($order);

        $order->update(['ready_sent_at' => now()]);
        $this->logEvent($order, 'ready_sent');

        return true;
    }

    /**
     * Generate work order text
     */
    public function generateWorkOrder(Order $order): string
    {
        $template = view('work-order.template', ['order' => $order->load([
            'client', 'therapist', 'slot', 'massageType', 'oilType', 'confirmation'
        ])])->render();

        $order->update([
            'work_order_status' => 'READY',
            'work_order_text' => $template,
        ]);

        $this->logEvent($order, 'work_order_generated');

        return $template;
    }

    /**
     * Mark work order as sent to therapist
     */
    public function markWorkOrderSent(Order $order, bool $viaTelegram = false): void
    {
        $order->update([
            'work_order_status' => 'SENT',
            'sent_to_therapist_at' => now(),
        ]);

        $this->logEvent($order, 'work_order_sent', ['method' => $viaTelegram ? 'telegram' : 'manual']);
    }

    /**
     * Fill quality assessment form
     */
    public function fillQuality(Order $order, array $data): OrderQuality
    {
        $quality = $order->quality()->updateOrCreate(
            ['order_id' => $order->id],
            array_merge($data, [
                'filled_by' => auth()->id(),
                'filled_at' => now(),
            ])
        );

        $this->logEvent($order, 'qa_filled', $data);

        return $quality;
    }

    /**
     * Complete order
     */
    public function complete(Order $order): void
    {
        if (!$order->quality) {
            throw new QARequiredException('Quality assessment must be filled before completing');
        }

        $order->update(['status' => Order::STATUS_COMPLETED]);
        $this->logEvent($order, 'completed');
    }

    /**
     * Cancel order
     */
    public function cancel(Order $order, string $reason): void
    {
        DB::transaction(function () use ($order, $reason) {
            // Release slot if pending
            if ($order->slot->status === Slot::STATUS_PENDING) {
                $this->slotService->release($order->slot);
            }

            $order->update(['status' => Order::STATUS_CANCELLED]);
            $this->logEvent($order, 'cancelled', ['reason' => $reason]);
        });
    }

    /**
     * Client requests cancellation (after RESERVED)
     */
    public function requestCancel(Order $order): void
    {
        if ($order->status !== Order::STATUS_RESERVED) {
            throw new InvalidStatusException('Can only request cancel for RESERVED orders');
        }

        $order->update(['status' => Order::STATUS_CANCEL_REQUESTED]);
        $this->logEvent($order, 'cancel_requested');
    }

    /**
     * Log order event
     */
    private function logEvent(Order $order, string $event, array $data = []): void
    {
        OrderAuditLog::create([
            'order_id' => $order->id,
            'event' => $event,
            'user_id' => auth()->id(),
            'data' => $data,
        ]);
    }
}
```

---

### Admin Controller Implementation

```php
// app/Http/Controllers/Admin/OrderController.php

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    public function index(Request $request)
    {
        $orders = Order::query()
            ->with(['client', 'therapist', 'slot', 'massageType'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->payment_status, fn($q, $s) => $q->where('payment_status', $s))
            ->when($request->therapist_id, fn($q, $id) => $q->where('therapist_id', $id))
            ->when($request->search, fn($q, $s) => $q->whereHas('client', fn($q) =>
                $q->where('phone', 'like', "%{$s}%")->orWhere('name', 'like', "%{$s}%")
            ))
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/Orders/Index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load([
            'client', 'therapist', 'slot', 'massageType', 'oilType',
            'confirmation', 'quality', 'auditLogs.user'
        ]);

        return Inertia::render('Admin/Orders/Show', compact('order'));
    }

    public function saveConfirmation(Order $order, Request $request)
    {
        $this->authorize('confirm orders');

        $validated = $request->validate([
            'address' => 'required|string',
            'entrance' => 'nullable|string',
            'floor' => 'nullable|string',
            'elevator' => 'boolean',
            'parking' => 'nullable|string',
            'landmark' => 'nullable|string',
            'onsite_phone' => 'nullable|string',
            'constraints' => 'nullable|string',
            'space_ok' => 'boolean',
            'pets' => 'boolean',
            'note_to_therapist' => 'nullable|string',
            'call_outcome' => 'required|in:CONFIRMED,RESCHEDULE,NO_ANSWER,CANCELLED',
        ]);

        $this->orderService->saveConfirmation($order, $validated);

        return back()->with('success', 'Confirmation saved');
    }

    public function createInvoice(Order $order, Request $request)
    {
        $this->authorize('manage payments');

        $validated = $request->validate([
            'provider' => 'required|in:payme,click',
            'amount' => 'nullable|integer|min:1000',
        ]);

        $result = $this->orderService->createInvoice(
            $order,
            $validated['provider'],
            $validated['amount'] ?? null
        );

        // Send SMS with payment link
        SmsService::send($order->client->phone, "Payment link: {$result['url']}");

        return back()->with('success', 'Invoice created and sent');
    }

    public function confirmBooking(Order $order)
    {
        $this->authorize('manage payments');
        $this->orderService->confirmBooking($order);
        return back()->with('success', 'Booking confirmed');
    }

    public function generateWorkOrder(Order $order)
    {
        $this->authorize('send work orders');
        $text = $this->orderService->generateWorkOrder($order);
        return response()->json(['text' => $text]);
    }

    public function sendWorkOrder(Order $order)
    {
        $this->authorize('send work orders');

        if ($order->therapist->telegram_chat_id) {
            TelegramService::sendToTherapist($order->therapist, $order->work_order_text);
            $this->orderService->markWorkOrderSent($order, true);
        }

        return back()->with('success', 'Work order sent');
    }

    public function fillQuality(Order $order, Request $request)
    {
        $this->authorize('fill qa');

        $validated = $request->validate([
            'rating_punctuality' => 'required|integer|min:1|max:10',
            'rating_quality' => 'required|integer|min:1|max:10',
            'rating_communication' => 'required|integer|min:1|max:10',
            'rating_overall' => 'required|integer|min:1|max:10',
            'will_order_again' => 'required|boolean',
            'recommend' => 'required|boolean',
            'hygiene_issue' => 'required|boolean',
            'hygiene_comment' => 'required_if:hygiene_issue,true|nullable|string',
            'improvement_comment' => 'nullable|string',
        ]);

        $this->orderService->fillQuality($order, $validated);

        return back()->with('success', 'QA saved');
    }

    public function complete(Order $order)
    {
        $this->authorize('fill qa');
        $this->orderService->complete($order);
        return back()->with('success', 'Order completed');
    }

    public function cancel(Order $order, Request $request)
    {
        $this->authorize('edit orders');

        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        $this->orderService->cancel($order, $validated['reason']);

        return back()->with('success', 'Order cancelled');
    }
}
```

---

### CSV Export

```php
// app/Http/Controllers/Admin/ExportController.php

public function orders(Request $request)
{
    $this->authorize('export orders');

    $validated = $request->validate([
        'from' => 'required|date',
        'to' => 'required|date|after_or_equal:from',
    ]);

    $orders = Order::with([
        'client', 'therapist', 'slot', 'massageType', 'oilType',
        'confirmation', 'quality'
    ])
        ->whereBetween('created_at', [$validated['from'], $validated['to'] . ' 23:59:59'])
        ->get();

    return response()->streamDownload(function () use ($orders) {
        $handle = fopen('php://output', 'w');

        // Header
        fputcsv($handle, [
            'Order ID', 'Created', 'Status', 'Payment Status',
            'Client Phone', 'Client Name',
            'Therapist', 'Massage Type', 'Oil Type',
            'Date', 'Time', 'Amount',
            'Address', 'Floor', 'Elevator', 'Parking', 'Constraints',
            'Paid At', 'Pay Method',
            'Rating Overall', 'Will Order Again', 'Recommend',
            'Hygiene Issue', 'Improvement Comment'
        ]);

        foreach ($orders as $order) {
            fputcsv($handle, [
                $order->id,
                $order->created_at->format('Y-m-d H:i'),
                $order->status,
                $order->payment_status,
                $order->client->phone,
                $order->client->name,
                $order->therapist->name,
                $order->massageType->name,
                $order->oilType?->name,
                $order->slot->date,
                $order->slot->start_time,
                $order->total_amount,
                $order->confirmation?->address,
                $order->confirmation?->floor,
                $order->confirmation?->elevator ? 'Yes' : 'No',
                $order->confirmation?->parking,
                $order->confirmation?->constraints,
                $order->paid_at?->format('Y-m-d H:i'),
                $order->pay_provider,
                $order->quality?->rating_overall,
                $order->quality?->will_order_again ? 'Yes' : 'No',
                $order->quality?->recommend ? 'Yes' : 'No',
                $order->quality?->hygiene_issue ? 'Yes' : 'No',
                $order->quality?->improvement_comment,
            ]);
        }

        fclose($handle);
    }, "orders-export-{$validated['from']}-{$validated['to']}.csv", [
        'Content-Type' => 'text/csv',
    ]);
}
```
