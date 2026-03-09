# 📋 Sabai - Development Plan

> **Yangilangan:** 2025-02-07 (TZ hujjatlari asosida)

## 📚 ТЗ Hujjatlar
- `/var/www/hm/docs/TZ_SLOT_MECHANISM.md` — Slot hisoblash algoritmi
- `/var/www/hm/docs/TZ_BOOKING_UI.md` — 3-qadamli Booking UI
- `/var/www/hm/docs/TZ_FULL_SPECS.md` — To'liq spetsifikatsiya

---

## 📊 Joriy Holat (Current State)

### ✅ Tayyor (Completed)
| Modul | Status | Izoh |
|-------|--------|------|
| ServiceTypes CRUD | ✅ | Massaj turlari (translatable) |
| Oils CRUD | ✅ | Yog' turlari |
| Masters CRUD | ✅ | Ustalar boshqaruvi |
| StandardItems CRUD | ✅ | Standart jihozlar |
| Dispatchers CRUD | ✅ | Dispetcherlar |
| Customers CRUD | ✅ | Mijozlar |
| Settings | ✅ | Sozlamalar |
| Slots CRUD | ✅ | Vaqt slotlari (template) |
| MasterSlotBooking | ✅ | Master-Slot bog'lash modeli |
| Order Model | ✅ | Buyurtma modeli (to'liq) |
| Clean Architecture | ✅ | Services, Repositories, FormRequests |

### ⏳ Kerak (Needed)
| Modul | Priority | Izoh |
|-------|----------|------|
| Master Schedule UI | P0 | Masterning kunlik ish grafigi |
| Orders Management | P0 | Buyurtmalarni boshqarish |
| Booking Flow API | P0 | Public booking endpoints |
| Telegram Integration | P1 | Xabarlar yuborish |
| Payments | P2 | Payme/Click integratsiya |

---

## 🚀 Asosiy O'zgarishlar (TZ asosida)

### Yangi Konsepsiya: "Arrival Window"
Mijoz **massaj boshlanish vaqtini** emas, **masterning kelish oynasini** (30 daqiqalik) tanlaydi.

**Vaqt hisoblash:**
```
SLOT_STEP = 30 min     // Kelish oynasi
TRAVEL = 30 min        // Yo'l
PRE = 10 min           // Tayyorgarlik
POST = 10 min          // Yig'ishtirish
INTER_CLIENT_BUFFER = 10 min  // 2+ kishi bo'lsa
```

**TOTAL_BUSY:**
- 60 min → 30+10+60+10 = **110 min**
- 90 min → 30+10+90+10 = **140 min**
- 120 min → 30+10+120+10 = **170 min**

### 3-Qadamli Booking Flow
```
[1. Услуга] → [2. Мастер и время] → [3. Оплата]
```

**Qadam 1 — Xizmat:**
- Massaj turi (Relax / Thai)
- Kuch (Soft / Medium / Hard / Any)
- Davomiylik (60 / 90 / 120)
- Kishilar soni (1-4)

**Qadam 2 — Master va Vaqt:**
- Master tanlash (Hammasi / Konkret)
- Sana tanlash
- Kelish oynasi tanlash
- Faqat mavjud slotlar ko'rsatiladi!

**Qadam 3 — To'lov:**
- Buyurtma tafsilotlari
- Payme / Click
- Oferta checkbox (majburiy)

---

## 🗓️ Sprint 2A: Admin Panel (Pulat talablari asosida)

### HM-A01: Master Schedule Management
**Maqsad:** Masterning ish vaqtlarini boshqarish

**UI Shablon (Pulat tavsifi):**
```
┌─────────────────────────────────────────────────┐
│ 📅 Master Schedule - Anvar                      │
├─────────────────────────────────────────────────┤
│ Ish vaqti: [08:00] dan [22:00] gacha           │
│                                                 │
│ 📆 Sana: [2025-02-10] [◀] [▶]                  │
│                                                 │
│ ┌─────┬─────┬─────┬─────┬─────┬─────┬─────┐   │
│ │08:00│09:00│10:00│11:00│12:00│13:00│14:00│   │
│ │ 🟢  │ 🟢  │ 🟡  │ 🔵  │ 🟢  │ 🟢  │ 🔴  │   │
│ └─────┴─────┴─────┴─────┴─────┴─────┴─────┘   │
│ ┌─────┬─────┬─────┬─────┬─────┬─────┬─────┐   │
│ │15:00│16:00│17:00│18:00│19:00│20:00│21:00│   │
│ │ 🟢  │ 🟢  │ 🟢  │ 🟢  │ 🟢  │ 🟢  │ 🟢  │   │
│ └─────┴─────┴─────┴─────┴─────┴─────┴─────┘   │
│                                                 │
│ Legend: 🟢 Bo'sh  🟡 Kutilmoqda  🔵 Band  🔴 Yopiq │
└─────────────────────────────────────────────────┘
```

**Funksiyalar:**
1. Masterning ish vaqtini belgilash (start_time, end_time)
2. Kunlik slotlarni ko'rish/boshqarish
3. Slotni yopish (BLOCKED) - dam olish, tushlik va h.k.
4. Slotni ochish (FREE)
5. Haftalik/oylik ko'rinish

**Fayllar:**
- `app/Http/Controllers/Admin/MasterScheduleController.php` ✅ (mavjud)
- `resources/js/Pages/Admin/Schedule/Index.vue`
- `resources/js/Components/Admin/SlotGrid.vue`

**Estimate:** 6h

---

### HM-A02: Orders Dashboard
**Maqsad:** Barcha buyurtmalarni ko'rish va boshqarish

**UI:**
```
┌─────────────────────────────────────────────────┐
│ 📦 Buyurtmalar                    [+ Yangi]     │
├─────────────────────────────────────────────────┤
│ 🔍 Qidirish...  [Status ▼] [Sana ▼] [Master ▼] │
├─────────────────────────────────────────────────┤
│ #HM-20250210-001 │ Yangi   │ Anvar │ 10:00     │
│ Ali +998901234567 │ Relax Oil │ 250,000 so'm   │
│ [Ko'rish] [Tasdiqlash] [Bekor qilish]          │
├─────────────────────────────────────────────────┤
│ #HM-20250210-002 │ Tasdiqlangan │ Bekzod │11:00│
│ Vali +998909876543 │ An'anaviy │ 200,000 so'm  │
│ [Ko'rish] [Jarayonga o'tkazish]                │
└─────────────────────────────────────────────────┘
```

**Funksiyalar:**
1. Buyurtmalar ro'yxati (filtrlash, qidirish)
2. Status o'zgartirish workflow:
   - NEW → CONFIRMING → CONFIRMED → IN_PROGRESS → COMPLETED
   - NEW → CANCELLED
3. Buyurtma tafsilotlari ko'rish
4. Master/slot o'zgartirish
5. Telegram xabar yuborish

**Fayllar:**
- `app/Http/Controllers/Admin/OrderController.php` ✅ (mavjud)
- `resources/js/Pages/Admin/Orders/Index.vue` ✅ (mavjud)
- `resources/js/Pages/Admin/Orders/Show.vue`
- `resources/js/Pages/Admin/Orders/Create.vue`

**Estimate:** 8h

---

### HM-A03: Quick Order Creation (Admin)
**Maqsad:** Admin orqali tez buyurtma yaratish

**UI Flow (Pulat tavsifi - 4 qadam):**

**1-qadam: Xizmat tanlash**
```
┌─────────────────────────────────────────────────┐
│ 1️⃣ Xizmat tanlang                              │
├─────────────────────────────────────────────────┤
│ ┌─────────────────┐ ┌─────────────────┐        │
│ │ An'anaviy       │ │ ● Relax Oil     │        │
│ │ massaj          │ │   massaj        │        │
│ │ 200,000 so'm    │ │   250,000 so'm  │        │
│ │ 1 soat          │ │   1.5 soat      │        │
│ └─────────────────┘ └─────────────────┘        │
│                                                 │
│ Yog' turi: [Coconut ▼]                         │
└─────────────────────────────────────────────────┘
```

**2-qadam: Master tanlash**
```
┌─────────────────────────────────────────────────┐
│ 2️⃣ Master tanlang                              │
├─────────────────────────────────────────────────┤
│ ┌───────┐ ┌───────┐ ┌───────┐                  │
│ │ 📷    │ │ 📷    │ │ 📷    │                  │
│ │ Anvar │ │Bekzod │ │ Jasur │                  │
│ │  ●    │ │  ○    │ │  ○    │                  │
│ └───────┘ └───────┘ └───────┘                  │
└─────────────────────────────────────────────────┘
```

**3-qadam: Vaqt tanlash**
```
┌─────────────────────────────────────────────────┐
│ 3️⃣ Vaqt tanlang                                │
├─────────────────────────────────────────────────┤
│ 📅 [10-Fev] [11-Fev] [12-Fev] [13-Fev] ...     │
│                                                 │
│ ┌─────┬─────┬─────┬─────┬─────┬─────┐         │
│ │10:00│11:00│12:00│14:00│15:00│16:00│         │
│ │ 🟢  │ 🟢  │ ❌  │ 🟢  │ 🟢  │ 🟢  │         │
│ └─────┴─────┴─────┴─────┴─────┴─────┘         │
│                                                 │
│ ⚠️ 20:00 dan keyin 1.5 soatlik massaj mumkin   │
│    emas (tugash vaqti: 22:00)                   │
└─────────────────────────────────────────────────┘
```

**4-qadam: To'lov va tasdiqlash**
```
┌─────────────────────────────────────────────────┐
│ 4️⃣ Tasdiqlash                                  │
├─────────────────────────────────────────────────┤
│ Xizmat: Relax Oil massaj                        │
│ Yog': Coconut                                   │
│ Master: Anvar                                   │
│ Sana: 10-Fevral, 14:00 - 15:30                 │
│                                                 │
│ ────────────────────────────────                │
│ Jami: 250,000 so'm                              │
│                                                 │
│ Mijoz telefoni: [+998 __ ___ __ __]            │
│ Ism (ixtiyoriy): [_______________]              │
│ Manzil: [____________________________]          │
│                                                 │
│ [Bekor qilish]              [✓ Tasdiqlash]     │
└─────────────────────────────────────────────────┘
```

**Logika (Pulat so'zlaridan):**
1. Master ish vaqti: 08:00 - 22:00
2. Agar massaj 2 soat bo'lsa → 20:00 dan keyin qabul qilib bo'lmaydi
3. Narx xizmat + davomiylik bo'yicha dinamik hisoblanadi
4. Slot tanlaganda pastda summa avtomatik yangilanadi

**Fayllar:**
- `resources/js/Pages/Admin/Orders/Create.vue`
- `resources/js/Components/Admin/ServiceSelector.vue`
- `resources/js/Components/Admin/MasterSelector.vue`
- `resources/js/Components/Admin/TimeSlotPicker.vue`
- `resources/js/Components/Admin/OrderSummary.vue`

**Estimate:** 10h

---

## 🗓️ Sprint 2B: Public Booking (3-Step Wizard)

### HM-B00: SlotCalculationService
**Maqsad:** TZ asosida slot mavjudligini hisoblash

```php
class SlotCalculationService
{
    const SLOT_STEP = 30;
    const TRAVEL = 30;
    const PRE = 10;
    const POST = 10;
    const INTER_CLIENT_BUFFER = 10;
    
    public function getAvailableSlots(
        Master $master,
        Carbon $date,
        array $serviceParams
    ): array;
    
    public function isSlotAvailable(
        Master $master,
        Carbon $date,
        string $windowStart,
        string $windowEnd,
        int $duration,
        int $peopleCount
    ): bool;
    
    public function calculateVisitCore(int $duration, int $peopleCount): int
    {
        $massageTotal = $duration * $peopleCount + self::INTER_CLIENT_BUFFER * ($peopleCount - 1);
        return self::PRE + $massageTotal + self::POST;
    }
}
```

**Estimate:** 6h

---

### HM-B01: Booking Step 1 - Service Selection
**UI:** Xizmat parametrlarini tanlash

```vue
<template>
  <div class="step-1-service">
    <!-- Massaj turi -->
    <div class="massage-types">
      <button v-for="type in types" :class="{active: selected.type === type.id}">
        {{ type.name }}
      </button>
    </div>
    
    <!-- Kuch -->
    <div class="pressure-levels">
      <chip v-for="level in ['soft','medium','hard','any']" />
    </div>
    
    <!-- Davomiylik -->
    <div class="durations">
      <chip v-for="d in [60, 90, 120]">{{ d }} min</chip>
    </div>
    
    <!-- Kishilar -->
    <div class="people-count">
      <chip v-for="n in [1,2,3,4]">{{ n }}</chip>
    </div>
    
    <button @click="nextStep">Davom etish</button>
  </div>
</template>
```

**Estimate:** 3h

---

### HM-B02: Booking Step 2 - Master & Time
**UI:** Master va vaqt tanlash (bitta ekranda)

```vue
<template>
  <div class="step-2-master-time">
    <!-- Xizmat xulosa -->
    <ServiceSummary :params="serviceParams" @edit="goBack" />
    
    <!-- Master carousel -->
    <MasterCarousel 
      :masters="availableMasters"
      v-model="selectedMaster"
      show-all-option
    />
    
    <!-- Sana lenta -->
    <DateStrip 
      :dates="next14Days"
      v-model="selectedDate"
      :availability="dateAvailability"
    />
    
    <!-- Slot grid -->
    <SlotGrid 
      :slots="availableSlots"
      v-model="selectedSlot"
    />
    
    <!-- Eslatma -->
    <ReminderCard :items="['Tinch xona', '2x2m joy', 'Kontraindikatsiya']" />
    
    <!-- Sticky footer -->
    <StickyFooter 
      :total="totalPrice"
      :disabled="!canProceed"
      @next="goToPayment"
    />
  </div>
</template>
```

**Logika:**
- "Hammasi" tanlansa → barcha masterlar uchun slotlar
- Slot tanlansa → masterlar filtrlanadi
- 1 ta master qolsa → avtomatik tanlanadi

**Estimate:** 8h

---

### HM-B03: Booking Step 3 - Payment
**UI:** To'lov va tasdiqlash

```vue
<template>
  <div class="step-3-payment">
    <!-- Buyurtma tafsilotlari -->
    <OrderDetails :order="orderSummary" />
    
    <!-- Manzil -->
    <AddressInput v-model="address" />
    
    <!-- To'lov usuli -->
    <PaymentMethods v-model="paymentMethod" />
    
    <!-- Oferta -->
    <label>
      <input type="checkbox" v-model="offerAccepted" />
      Men oferta shartlarini qabul qilaman
    </label>
    
    <!-- To'lov tugmasi -->
    <button :disabled="!offerAccepted" @click="pay">
      To'lash - {{ totalPrice | currency }}
    </button>
  </div>
</template>
```

**Estimate:** 4h

---

## 🗓️ Sprint 2C: Public Booking API

### HM-B01: Masters API
```
GET /api/v1/masters
GET /api/v1/masters/{id}
GET /api/v1/masters/{id}/slots?date=YYYY-MM-DD
```

### HM-B02: Services API
```
GET /api/v1/services
GET /api/v1/services/{id}
```

### HM-B03: Booking API
```
POST /api/v1/bookings
{
  "master_id": 1,
  "service_type_id": 2,
  "oil_id": 1,
  "slot_id": 5,
  "date": "2025-02-10",
  "contact_phone": "+998901234567",
  "contact_name": "Ali",
  "address": "Toshkent, Chilonzor"
}
```

**Estimate:** 6h

---

## 🗓️ Sprint 2C: Telegram Integration

### HM-C01: TelegramService
- Yangi buyurtma xabari → OPS group
- Status o'zgarishi → Customer
- Eslatma → Master

### HM-C02: Notification Templates
```
🆕 YANGI BUYURTMA

📋 #HM-20250210-001
👤 +998901234567 (Ali)
💆 Relax Oil massaj (Coconut)
🧑‍⚕️ Master: Anvar
📅 10-Fev, 14:00 - 15:30
📍 Chilonzor, ...

💰 250,000 so'm
```

**Estimate:** 4h

---

## 📊 Timeline

| Sprint | Tasks | Hours | Deadline |
|--------|-------|-------|----------|
| 2A | Admin Panel (Schedule, Orders, Quick Create) | 24h | +3 kun |
| 2B | Public Booking API | 6h | +1 kun |
| 2C | Telegram Integration | 4h | +1 kun |

**Jami:** ~34h (~5 ish kuni)

---

## 🎯 Keyingi Qadamlar (Priority Order)

1. **HM-A01: Master Schedule UI** - Masterlarning ish grafikini boshqarish
2. **HM-A03: Quick Order Creation** - Admin orqali buyurtma yaratish (Pulat shablon)
3. **HM-A02: Orders Dashboard** - Buyurtmalarni boshqarish
4. **HM-B01-03: Public API** - Telegram Mini App uchun
5. **HM-C01-02: Telegram** - Xabarlar

---

## 🔧 Texnik Eslatmalar

### Slot Availability Logic
```php
// Slot mavjudligini tekshirish
public function isSlotAvailable(Master $master, Slot $slot, Carbon $date): bool
{
    // 1. Master ish vaqti ichidami?
    if ($slot->start_time < $master->work_start || $slot->end_time > $master->work_end) {
        return false;
    }
    
    // 2. 6 soat qoidasi
    $slotDateTime = $date->copy()->setTimeFromTimeString($slot->start_time);
    if ($slotDateTime->diffInHours(now()) < 6) {
        return false;
    }
    
    // 3. Slot band emasmi?
    $booking = MasterSlotBooking::forMaster($master->id)
        ->forDate($date)
        ->where('slot_id', $slot->id)
        ->first();
    
    return !$booking || $booking->status === 'FREE';
}

// Massaj tugash vaqti tekshirish
public function canBookService(Master $master, ServiceType $service, Slot $slot): bool
{
    $endTime = Carbon::parse($slot->start_time)->addMinutes($service->duration);
    $workEnd = Carbon::parse($master->work_end);
    
    return $endTime->lte($workEnd);
}
```

### Dynamic Price Calculation
```php
public function calculatePrice(ServiceType $service, ?Oil $oil = null): int
{
    $price = $service->price;
    
    if ($oil && $oil->price_modifier) {
        $price += $oil->price_modifier;
    }
    
    return $price;
}
```

---

## ✅ Tasdiq Uchun

- [ ] Plan ko'rib chiqildi
- [ ] Priority tasdiqlandi
- [ ] Timeline maqbul
- [ ] Boshlash mumkin

**Tayyorladi:** Assist
**Sana:** 2025-02-07
