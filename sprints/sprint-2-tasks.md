# 📋 Sprint 2: Public Booking - Trello Tasks

## Sprint Info
- **Sprint:** 2
- **Focus:** Public Booking Flow + Telegram
- **Dates:** 10-16 Fevral 2025
- **Delivery:** 16-Fevral (Yakshanba)
- **Hours:** ~22h work + ~3h buffer

---

## GT-015: Base UI Components

**📝 Description:**
Reusable Vue componentlarni yaratish.

**✅ Acceptance Criteria:**
- [ ] BaseButton (primary, secondary, outline, disabled states)
- [ ] BaseInput (label, error, validation states)
- [ ] BaseCard (shadow, border variants)
- [ ] BaseChip (colors for slot statuses)
- [ ] LoadingSpinner
- [ ] ErrorMessage

**🔧 Props/Variants:**
```vue
<BaseButton variant="primary|secondary|outline" :loading="bool" :disabled="bool" />
<BaseChip color="green|yellow|gray|red" />
```

**⏱ Estimate:** 3h | **🏷 Labels:** `frontend`, `components`, `P0`

---

## GT-016: Landing Page

**📝 Description:**
Mobile-first landing page yaratish.

**✅ Acceptance Criteria:**
- [ ] Hero section (title, subtitle, CTA button)
- [ ] Services section (massage types, prices)
- [ ] Masters preview (3-4 cards)
- [ ] How it works (3 steps)
- [ ] Footer (contacts, links)
- [ ] Mobile responsive

**🎨 Design:**
- Pencil mockup asosida
- Primary color: Blue
- Mobile-first approach

**⏱ Estimate:** 3h | **🏷 Labels:** `frontend`, `UI`, `P0`

---

## GT-017: Masters List Page

**📝 Description:**
Ustalar ro'yxati sahifasi.

**✅ Acceptance Criteria:**
- [ ] GET /api/v1/masters dan data olish
- [ ] Master cards grid (2 columns mobile)
- [ ] Card: photo, name, bio (truncated)
- [ ] Click → Master detail page
- [ ] Loading skeleton
- [ ] Empty state

**🔧 API:**
```
GET /api/v1/masters
Response: { data: [{ id, name, bio, photo }] }
```

**⏱ Estimate:** 2h | **🏷 Labels:** `frontend`, `UI`, `P0`

---

## GT-018: Master Detail Page

**📝 Description:**
Usta haqida to'liq ma'lumot va slotlar.

**✅ Acceptance Criteria:**
- [ ] Master info header (photo, name, full bio)
- [ ] Date selector component
- [ ] Integration with slots calendar
- [ ] Back button (TMA)

**⏱ Estimate:** 2h | **🏷 Labels:** `frontend`, `UI`, `P0`

---

## GT-019: Date Selector Component

**📝 Description:**
7 kunlik date picker.

**✅ Acceptance Criteria:**
- [ ] Horizontal scroll chips
- [ ] 7 kun (bugundan boshlab)
- [ ] Selected state
- [ ] Day name + date format
- [ ] Today highlight

**🎨 Design:**
```
[Bugun 10] [Sesh 11] [Chor 12] [Pay 13] [Juma 14] [Shan 15] [Yak 16]
```

**⏱ Estimate:** 1.5h | **🏷 Labels:** `frontend`, `components`, `P0`

---

## GT-020: Slots Calendar Component

**📝 Description:**
Vaqt slotlari ko'rsatish.

**✅ Acceptance Criteria:**
- [ ] GET /api/v1/masters/{id}/slots?date=YYYY-MM-DD
- [ ] Grid layout (time slots)
- [ ] Status colors (FREE=green, PENDING=yellow, RESERVED=gray, BLOCKED=red)
- [ ] 6-hour rule (disable if < 6h from now)
- [ ] Click to select (only FREE)
- [ ] Selected state highlight
- [ ] Loading state
- [ ] Empty state ("Bu kunda slot yo'q")

**🔧 API:**
```
GET /api/v1/masters/1/slots?date=2025-02-10
Response: { data: [{ id, start_time, end_time, status }] }
```

**🎨 Slot Card:**
```
┌─────────────┐
│  10:00      │  ← start_time
│  FREE       │  ← status badge
└─────────────┘
```

**⏱ Estimate:** 4h | **🏷 Labels:** `frontend`, `components`, `P0`

---

## GT-021: Slots API Endpoint

**📝 Description:**
Master slotlarini qaytaruvchi API.

**✅ Acceptance Criteria:**
- [ ] GET /api/v1/masters/{master}/slots
- [ ] Query params: date (required)
- [ ] Response: slots with status
- [ ] 6-hour rule applied (mark as unavailable)
- [ ] Active masters only

**🔧 Controller:**
```php
public function index(Master $master, Request $request)
{
    $date = $request->validate(['date' => 'required|date'])['date'];
    
    $slots = $master->slots()
        ->forDate($date)
        ->orderBy('start_time')
        ->get()
        ->map(function ($slot) {
            // Apply 6-hour rule
            if ($slot->status === SlotStatus::FREE) {
                $slotTime = Carbon::parse($slot->date . ' ' . $slot->start_time);
                if ($slotTime->diffInHours(now()) < 6) {
                    $slot->status = 'UNAVAILABLE';
                }
            }
            return $slot;
        });
    
    return response()->json(['data' => $slots]);
}
```

**⏱ Estimate:** 2h | **🏷 Labels:** `backend`, `api`, `P0`

---

## GT-022: Massage Type Selection

**📝 Description:**
Massaj turi va yog' tanlash.

**✅ Acceptance Criteria:**
- [ ] Massage type cards (Traditional, Relax Oil)
- [ ] Price display
- [ ] Oil preference (if Relax selected)
- [ ] Oil options: Coconut, Lavender, No preference
- [ ] Selected state

**🎨 Design:**
```
┌─────────────────────────┐
│ 💆 An'anaviy massaj     │
│ 200,000 so'm            │
│ ○ (radio)               │
└─────────────────────────┘
┌─────────────────────────┐
│ 🧴 Relax yog'li         │
│ 250,000 so'm            │
│ ● (selected)            │
│                         │
│ Yog' turi:              │
│ [Coconut] [Lavender] [X]│
└─────────────────────────┘
```

**⏱ Estimate:** 2h | **🏷 Labels:** `frontend`, `UI`, `P0`

---

## GT-023: Fixed Bottom Bar

**📝 Description:**
Booking uchun fixed bottom bar.

**✅ Acceptance Criteria:**
- [ ] Fixed position bottom
- [ ] Selected slot info
- [ ] Total price
- [ ] "Davom etish" button
- [ ] Disabled until slot selected
- [ ] Safe area padding (mobile)

**🎨 Design:**
```
┌─────────────────────────────────┐
│ 10:00, 10-Fev  │  250,000 so'm │
│                │  [Davom etish]│
└─────────────────────────────────┘
```

**⏱ Estimate:** 1.5h | **🏷 Labels:** `frontend`, `UI`, `P0`

---

## GT-024: Booking Form

**📝 Description:**
Buyurtma yaratish formasi.

**✅ Acceptance Criteria:**
- [ ] Phone input (required, UZ format +998)
- [ ] Name input (optional)
- [ ] Comment textarea (optional)
- [ ] PD consent checkbox (required)
- [ ] Form validation
- [ ] Honeypot field (anti-spam)
- [ ] Submit button with loading

**🔧 Validation:**
```javascript
phone: required, regex: /^\+998[0-9]{9}$/
name: optional, max:100
comment: optional, max:500
pd_consent: required, accepted
```

**⏱ Estimate:** 3h | **🏷 Labels:** `frontend`, `forms`, `P0`

---

## GT-025: Order Creation API

**📝 Description:**
Buyurtma yaratish API endpoint.

**✅ Acceptance Criteria:**
- [ ] POST /api/v1/orders
- [ ] Validate slot availability
- [ ] Double booking prevention (DB transaction + lock)
- [ ] 6-hour rule server-side validation
- [ ] Create/find customer
- [ ] Create order
- [ ] Update slot: FREE → PENDING
- [ ] Return order with public_token
- [ ] Trigger Telegram notification (async)

**🔧 Request:**
```json
{
  "master_id": 1,
  "slot_id": 5,
  "massage_type": "relax_oil",
  "oil_preference": "coconut",
  "contact_phone": "+998901234567",
  "contact_name": "Ali",
  "comment": "...",
  "pd_consent": true
}
```

**🔧 Response:**
```json
{
  "success": true,
  "data": {
    "order_number": "GT-20250210-001",
    "public_token": "abc123...",
    "status": "NEW"
  }
}
```

**🔧 Service Logic:**
```php
DB::transaction(function () {
    // Lock slot for update
    $slot = Slot::lockForUpdate()->find($slotId);
    
    if ($slot->status !== SlotStatus::FREE) {
        throw new SlotNotAvailableException();
    }
    
    // 6-hour check
    $slotTime = Carbon::parse($slot->date . ' ' . $slot->start_time);
    if ($slotTime->diffInHours(now()) < 6) {
        throw new SlotTooSoonException();
    }
    
    // Create order
    $order = Order::create([...]);
    
    // Update slot
    $slot->update(['status' => SlotStatus::PENDING]);
    
    return $order;
});
```

**⏱ Estimate:** 4h | **🏷 Labels:** `backend`, `api`, `P0`

---

## GT-026: Order Success/Error Pages

**📝 Description:**
Buyurtma natijasi sahifalari.

**✅ Acceptance Criteria:**
- [ ] Success page (order number, next steps)
- [ ] Error page (try again button)
- [ ] Slot taken error (specific message)
- [ ] TMA close button

**🎨 Success:**
```
✅ Buyurtma qabul qilindi!

Buyurtma raqami: GT-20250210-001

Tez orada operator siz bilan bog'lanadi.

[Yopish]
```

**⏱ Estimate:** 1.5h | **🏷 Labels:** `frontend`, `UI`, `P0`

---

## GT-027: Telegram Service

**📝 Description:**
Telegram bot xabar yuborish service.

**✅ Acceptance Criteria:**
- [ ] TelegramService class
- [ ] Send message method
- [ ] Message formatting (Markdown)
- [ ] Error handling
- [ ] Retry logic
- [ ] Log to telegram_messages table

**🔧 Service:**
```php
class TelegramService
{
    public function sendToOpsGroup(string $message): bool
    {
        return $this->send(
            config('services.telegram.ops_bot_token'),
            config('services.telegram.ops_group_id'),
            $message
        );
    }
    
    public function sendToTherapistGroup(string $message): bool
    {
        return $this->send(
            config('services.telegram.therapist_bot_token'),
            config('services.telegram.therapist_group_id'),
            $message
        );
    }
}
```

**⏱ Estimate:** 2h | **🏷 Labels:** `backend`, `telegram`, `P0`

---

## GT-028: NEW Order Notification

**📝 Description:**
Yangi buyurtma haqida Telegram xabar.

**✅ Acceptance Criteria:**
- [ ] Trigger on order creation
- [ ] Send to OPS group
- [ ] Message includes: order number, customer phone, master, date/time, massage type
- [ ] Admin link to order
- [ ] Queue job (async)

**🔧 Message Template:**
```
🆕 YANGI BUYURTMA

📋 GT-20250210-001
👤 +998901234567 (Ali)
💆 Relax yog'li massaj
🧑‍⚕️ Master: Anvar
📅 10-Fev, 10:00

💰 250,000 so'm

🔗 Admin: https://admin.goldentouch.uz/orders/123
```

**🔧 Job:**
```php
class SendNewOrderNotification implements ShouldQueue
{
    public function handle()
    {
        $message = $this->formatMessage($this->order);
        app(TelegramService::class)->sendToOpsGroup($message);
        
        TelegramMessage::create([
            'order_id' => $this->order->id,
            'message_type' => 'NEW',
            'bot' => 'ops',
            'status' => 'SENT'
        ]);
    }
}
```

**⏱ Estimate:** 2h | **🏷 Labels:** `backend`, `telegram`, `P1`

---

## 📊 Summary

| ID | Task | Hours |
|----|------|-------|
| GT-015 | Base UI Components | 3h |
| GT-016 | Landing Page | 3h |
| GT-017 | Masters List | 2h |
| GT-018 | Master Detail | 2h |
| GT-019 | Date Selector | 1.5h |
| GT-020 | Slots Calendar | 4h |
| GT-021 | Slots API | 2h |
| GT-022 | Massage Selection | 2h |
| GT-023 | Bottom Bar | 1.5h |
| GT-024 | Booking Form | 3h |
| GT-025 | Order Creation API | 4h |
| GT-026 | Success/Error Pages | 1.5h |
| GT-027 | Telegram Service | 2h |
| GT-028 | NEW Notification | 2h |

**Total: ~22h + 3h buffer = ~25h**
