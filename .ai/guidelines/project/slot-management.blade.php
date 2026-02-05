## Golden Touch - Slot Management System

This document covers the slot status management and booking rules.

---

### Slot Statuses

| Status | Code | Bookable | Display | Description |
|--------|------|----------|---------|-------------|
| Free | `FREE` | Yes | Active button | Available for booking |
| Pending | `PENDING` | No | "In progress" | Order created, awaiting confirmation |
| Reserved | `RESERVED` | No | "Booked" | Paid and confirmed |
| Blocked | `BLOCKED` | No | "Closed" | Manually blocked |

---

### Status Transitions

```
┌─────────────────────────────────────────────────────────┐
│                     SLOT LIFECYCLE                       │
└─────────────────────────────────────────────────────────┘

                    ┌──────────┐
         ┌─────────→│   FREE   │←─────────┐
         │          └────┬─────┘          │
         │               │                │
    Cancel/NoAnswer      │ Order Created  │ Admin Unblock
         │               ▼                │
         │          ┌──────────┐          │
         └──────────│ PENDING  │──────────┘
                    └────┬─────┘
                         │
                         │ Payment Confirmed
                         │ + Booking Confirmed
                         ▼
                    ┌──────────┐
                    │ RESERVED │←───── Cancel with refund
                    └────┬─────┘       (rare, manual only)
                         │
                         │ Service Completed
                         ▼
                    ┌──────────┐
                    │ (stays)  │ Slot remains RESERVED
                    └──────────┘   for audit trail


                    ┌──────────┐
      Admin Block → │ BLOCKED  │ ← Can block FREE only
                    └──────────┘   Cannot block RESERVED
```

---

### Transition Rules

#### FREE → PENDING
- **Trigger**: Client submits booking form
- **Condition**: Slot must be FREE, ≥2 hours lead time
- **Action**: Create Order, link slot, send Telegram NEW

```php
// SlotService.php
public function markPending(Slot $slot, Order $order): void
{
    DB::transaction(function () use ($slot, $order) {
        $slot->refresh(); // Reload to check current status

        if ($slot->status !== Slot::STATUS_FREE) {
            throw new SlotNotAvailableException('Slot is no longer available');
        }

        if ($slot->start_datetime->diffInHours(now()) < 2) {
            throw new SlotNotAvailableException('Minimum 2 hours notice required');
        }

        $slot->update([
            'status' => Slot::STATUS_PENDING,
            'order_id' => $order->id,
        ]);
    });
}
```

#### PENDING → FREE
- **Trigger**: Call outcome is "No answer" or "Cancelled"
- **Condition**: Slot is PENDING
- **Action**: Release slot, update order status

```php
public function release(Slot $slot): void
{
    if ($slot->status !== Slot::STATUS_PENDING) {
        throw new InvalidSlotTransitionException('Can only release PENDING slots');
    }

    $slot->update([
        'status' => Slot::STATUS_FREE,
        'order_id' => null,
    ]);
}
```

#### PENDING → RESERVED
- **Trigger**: Dispatcher confirms booking after payment
- **Condition**: Order payment_status is PAID, slot is PENDING
- **Action**: Reserve slot, send Telegram READY (if conditions met)

```php
public function markReserved(Slot $slot, Order $order): void
{
    DB::transaction(function () use ($slot, $order) {
        if ($slot->status !== Slot::STATUS_PENDING) {
            throw new InvalidSlotTransitionException('Slot must be PENDING');
        }

        if ($order->payment_status !== Order::PAY_PAID) {
            throw new PaymentRequiredException('Payment must be confirmed first');
        }

        $slot->update(['status' => Slot::STATUS_RESERVED]);
        $order->update(['status' => Order::STATUS_RESERVED]);

        // Check if READY notification should be sent
        $this->checkAndSendReady($order);
    });
}
```

#### FREE → BLOCKED (Admin only)
- **Trigger**: Admin blocks slot (day off, unavailable)
- **Condition**: Slot is FREE
- **Action**: Block slot

```php
public function block(Slot $slot, string $reason = null): void
{
    if (!in_array($slot->status, [Slot::STATUS_FREE])) {
        throw new InvalidSlotTransitionException('Can only block FREE slots');
    }

    $slot->update([
        'status' => Slot::STATUS_BLOCKED,
        'block_reason' => $reason,
    ]);
}
```

#### BLOCKED → FREE (Admin only)
- **Trigger**: Admin unblocks slot
- **Condition**: Slot is BLOCKED
- **Action**: Restore to FREE

```php
public function unblock(Slot $slot): void
{
    if ($slot->status !== Slot::STATUS_BLOCKED) {
        throw new InvalidSlotTransitionException('Slot is not blocked');
    }

    $slot->update([
        'status' => Slot::STATUS_FREE,
        'block_reason' => null,
    ]);
}
```

---

### Slot Generation

Slots are pre-generated for therapists based on their schedule.

```php
// SlotService.php
public function generateSlotsForDate(Therapist $therapist, Carbon $date): void
{
    $workingHours = config('booking.working_hours', ['09:00', '21:00']);
    $duration = config('booking.session_duration', 60); // minutes
    $gap = config('booking.session_gap', 30); // minutes
    $interval = $duration + $gap; // 90 minutes between slot starts

    $start = $date->copy()->setTimeFromTimeString($workingHours[0]);
    $end = $date->copy()->setTimeFromTimeString($workingHours[1]);

    while ($start->copy()->addMinutes($duration)->lte($end)) {
        Slot::firstOrCreate([
            'therapist_id' => $therapist->id,
            'date' => $date->toDateString(),
            'start_time' => $start->format('H:i:s'),
        ], [
            'end_time' => $start->copy()->addMinutes($duration)->format('H:i:s'),
            'status' => Slot::STATUS_FREE,
        ]);

        $start->addMinutes($interval);
    }
}

// Example: Generate for next 7 days
public function generateUpcomingSlots(Therapist $therapist, int $days = 7): void
{
    for ($i = 0; $i < $days; $i++) {
        $this->generateSlotsForDate($therapist, now()->addDays($i));
    }
}
```

---

### Slot Display in Frontend

#### API Response
```php
// SlotController.php
public function index(Therapist $therapist, Request $request)
{
    $date = $request->get('date', now()->toDateString());

    $slots = Slot::where('therapist_id', $therapist->id)
        ->whereDate('date', $date)
        ->orderBy('start_time')
        ->get()
        ->map(fn($slot) => [
            'id' => $slot->id,
            'time' => $slot->start_time->format('H:i'),
            'status' => $slot->status,
            'bookable' => $slot->canBook(),
            'label' => $slot->getStatusLabel(),
        ]);

    return response()->json(['slots' => $slots]);
}
```

#### Slot Model Methods
```php
// Slot.php
public function canBook(): bool
{
    if ($this->status !== self::STATUS_FREE) {
        return false;
    }

    $leadTimeHours = config('booking.lead_time_hours', 2);
    return $this->start_datetime->diffInHours(now(), false) >= $leadTimeHours;
}

public function getStartDatetimeAttribute(): Carbon
{
    return Carbon::parse($this->date . ' ' . $this->start_time);
}

public function getStatusLabel(): string
{
    return match($this->status) {
        self::STATUS_FREE => 'Available',
        self::STATUS_PENDING => 'In progress',
        self::STATUS_RESERVED => 'Booked',
        self::STATUS_BLOCKED => 'Closed',
    };
}
```

---

### Double-Booking Prevention

#### Race Condition Handling
```php
// OrderService.php
public function createOrder(array $data): Order
{
    return DB::transaction(function () use ($data) {
        // Lock the slot row for update
        $slot = Slot::where('id', $data['slot_id'])
            ->lockForUpdate()
            ->first();

        if (!$slot || !$slot->canBook()) {
            throw new SlotNotAvailableException(
                'This time is unavailable. Please choose another slot.'
            );
        }

        // Create order
        $order = Order::create([
            'client_id' => $data['client_id'],
            'therapist_id' => $slot->therapist_id,
            'slot_id' => $slot->id,
            'massage_type_id' => $data['massage_type_id'],
            'oil_type_id' => $data['oil_type_id'] ?? null,
            'status' => Order::STATUS_NEW,
            'payment_status' => Order::PAY_NOT_INVOICED,
            'total_amount' => $data['total_amount'],
            'public_token' => Str::random(32),
        ]);

        // Mark slot as pending
        $slot->update([
            'status' => Slot::STATUS_PENDING,
            'order_id' => $order->id,
        ]);

        // Log and notify
        $order->logEvent('created');
        TelegramService::sendNew($order);

        return $order;
    });
}
```

---

### Bulk Operations

#### Block Multiple Slots (Day Off)
```php
public function blockDay(Therapist $therapist, Carbon $date, string $reason): void
{
    Slot::where('therapist_id', $therapist->id)
        ->whereDate('date', $date)
        ->where('status', Slot::STATUS_FREE)
        ->update([
            'status' => Slot::STATUS_BLOCKED,
            'block_reason' => $reason,
        ]);
}
```

#### Release Expired Pending Slots
```php
// Scheduled task: Release slots where order wasn't confirmed within time limit
public function releaseExpiredPending(): void
{
    $timeout = config('booking.pending_timeout_minutes', 30);

    $expiredSlots = Slot::where('status', Slot::STATUS_PENDING)
        ->whereHas('order', function ($q) use ($timeout) {
            $q->where('created_at', '<', now()->subMinutes($timeout))
              ->whereIn('status', [Order::STATUS_NEW]);
        })
        ->get();

    foreach ($expiredSlots as $slot) {
        $this->release($slot);
        $slot->order->update(['status' => Order::STATUS_CANCELLED]);
        $slot->order->logEvent('auto_cancelled', ['reason' => 'Pending timeout']);
    }
}
```

---

### Configuration

```php
// config/booking.php
return [
    'session_duration' => env('SESSION_DURATION_MINUTES', 60),
    'session_gap' => env('SESSION_GAP_MINUTES', 30),
    'lead_time_hours' => env('BOOKING_LEAD_TIME_HOURS', 2),
    'pending_timeout_minutes' => env('PENDING_TIMEOUT_MINUTES', 30),
    'working_hours' => [
        'start' => env('WORKING_HOURS_START', '09:00'),
        'end' => env('WORKING_HOURS_END', '21:00'),
    ],
    'max_slots_per_order' => env('MAX_SLOTS_PER_ORDER', 6),
];
```

---

### Testing Slots

```php
// tests/Feature/SlotTest.php
public function test_cannot_book_non_free_slot()
{
    $slot = Slot::factory()->pending()->create();

    $response = $this->postJson('/api/orders', [
        'slot_id' => $slot->id,
        // ... other data
    ]);

    $response->assertStatus(422)
        ->assertJson(['message' => 'This time is unavailable. Please choose another slot.']);
}

public function test_cannot_book_slot_within_lead_time()
{
    $slot = Slot::factory()->create([
        'date' => now()->toDateString(),
        'start_time' => now()->addHour()->format('H:i:s'), // Only 1 hour ahead
        'status' => Slot::STATUS_FREE,
    ]);

    $response = $this->postJson('/api/orders', [
        'slot_id' => $slot->id,
        // ... other data
    ]);

    $response->assertStatus(422)
        ->assertJson(['message' => 'Bookings require at least 2 hours notice.']);
}

public function test_slot_becomes_pending_on_order_creation()
{
    $slot = Slot::factory()->free()->create([
        'date' => now()->addDay()->toDateString(),
    ]);

    $response = $this->postJson('/api/orders', [
        'slot_id' => $slot->id,
        // ... other data
    ]);

    $response->assertStatus(201);
    $this->assertEquals(Slot::STATUS_PENDING, $slot->fresh()->status);
}
```
