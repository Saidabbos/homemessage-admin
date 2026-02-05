# 📋 Sprint 3: Admin + Payments - Trello Tasks

## Sprint Info
- **Sprint:** 3
- **Focus:** Admin Panel + Payment Integrations
- **Dates:** 17-23 Fevral 2025
- **Delivery:** 23-Fevral (Yakshanba)
- **Hours:** ~22h work + ~3h buffer

---

## GT-029: Admin Login Page

**📝 Description:**
Admin panel login sahifasi.

**✅ Acceptance Criteria:**
- [ ] Email input
- [ ] Password input
- [ ] Login button with loading
- [ ] Error message display
- [ ] Redirect to dashboard on success
- [ ] Store token in localStorage

**🔧 API Call:**
```javascript
POST /api/v1/admin/login
{ email, password }
→ { user, token }
```

**⏱ Estimate:** 1.5h | **🏷 Labels:** `frontend`, `admin`, `P0`

---

## GT-030: Admin Layout

**📝 Description:**
Admin panel asosiy layout.

**✅ Acceptance Criteria:**
- [ ] Sidebar navigation (Orders, Masters, Slots, Settings)
- [ ] Header (user info, logout)
- [ ] Main content area
- [ ] Mobile responsive (collapsible sidebar)
- [ ] Active route highlight

**🎨 Layout:**
```
┌──────────┬─────────────────────────────┐
│ SIDEBAR  │  HEADER                     │
│          ├─────────────────────────────┤
│ Orders   │                             │
│ Masters  │     CONTENT AREA            │
│ Slots    │                             │
│ Settings │                             │
└──────────┴─────────────────────────────┘
```

**⏱ Estimate:** 2h | **🏷 Labels:** `frontend`, `admin`, `P0`

---

## GT-031: Orders List Page

**📝 Description:**
Buyurtmalar ro'yxati sahifasi.

**✅ Acceptance Criteria:**
- [ ] Table view (order_number, customer, master, date, status, actions)
- [ ] Status badges with colors
- [ ] Filters: status, master, date range
- [ ] Search by phone/order number
- [ ] Pagination
- [ ] Click row → Order detail
- [ ] Loading state

**🔧 API:**
```
GET /api/v1/admin/orders?status=NEW&master_id=1&date_from=&date_to=&search=&page=1
```

**🎨 Table:**
```
| # | Order | Customer | Master | Date | Status | Actions |
|---|-------|----------|--------|------|--------|---------|
| 1 | GT-.. | +998...  | Anvar  | 10/02| [NEW]  | [View]  |
```

**⏱ Estimate:** 4h | **🏷 Labels:** `frontend`, `admin`, `P0`

---

## GT-032: Order Card Page - Part 1

**📝 Description:**
Buyurtma boshqaruv sahifasi (Block A-D).

**✅ Acceptance Criteria:**
- [ ] **Block A - Client Info:** phone, name, orders count
- [ ] **Block B - Order Details:** order_number, massage_type, price, date/time, master
- [ ] **Block C - Confirmation Form:** address inputs, pi_pi checkbox, call outcome
- [ ] **Block D - Payment Section:** payment status, create invoice button, payment link

**🎨 Layout:**
```
┌─────────────────────────────────────────┐
│ Block A: Client                         │
│ +998901234567 | Ali | 3 orders          │
├─────────────────────────────────────────┤
│ Block B: Order                          │
│ GT-20250210-001 | Relax | 250,000       │
│ 10-Fev 10:00 | Master: Anvar            │
├─────────────────────────────────────────┤
│ Block C: Confirmation                   │
│ Address: [______________]               │
│ Entrance: [__] Floor: [__] Apt: [__]    │
│ Landmark: [______________]              │
│ □ Has restrictions (pi-pi)              │
│ Call: (•) Confirmed ( ) No answer       │
├─────────────────────────────────────────┤
│ Block D: Payment                        │
│ Status: PENDING                         │
│ [Create Payme Invoice] [Create Click]   │
│ Link: _______________  [Copy] [Send]    │
└─────────────────────────────────────────┘
```

**⏱ Estimate:** 4h | **🏷 Labels:** `frontend`, `admin`, `P0`

---

## GT-033: Order Card Page - Part 2

**📝 Description:**
Buyurtma boshqaruv sahifasi (Block E-G).

**✅ Acceptance Criteria:**
- [ ] **Block E - Reservation:** slot status, reserve/unreserve buttons
- [ ] **Block F - Digital Work Order:** summary for master (read-only)
- [ ] **Block G - QA Section:** rating form (after completion)
- [ ] Action buttons: Confirm, Cancel, Complete
- [ ] Status change confirmation modals

**🎨 Layout:**
```
┌─────────────────────────────────────────┐
│ Block E: Reservation                    │
│ Slot: PENDING  [Reserve] [Cancel]       │
├─────────────────────────────────────────┤
│ Block F: Work Order                     │
│ ┌─────────────────────────────────────┐ │
│ │ Master: Anvar                       │ │
│ │ Date: 10-Fev 10:00                  │ │
│ │ Client: Ali, +998901234567          │ │
│ │ Address: Chilanzar, 5-dom...        │ │
│ │ Type: Relax, Coconut oil            │ │
│ │ Notes: ...                          │ │
│ └─────────────────────────────────────┘ │
│ [Send to Master] [Resend READY]         │
├─────────────────────────────────────────┤
│ Block G: Quality Control (if completed) │
│ Overall: ⭐⭐⭐⭐⭐                      │
│ Punctuality: ⭐⭐⭐⭐☆                  │
│ ...                                     │
└─────────────────────────────────────────┘
```

**⏱ Estimate:** 3h | **🏷 Labels:** `frontend`, `admin`, `P0`

---

## GT-034: Order Admin API

**📝 Description:**
Order boshqaruv API endpoints.

**✅ Acceptance Criteria:**
- [ ] GET /orders - list with filters
- [ ] GET /orders/{id} - full details
- [ ] PUT /orders/{id} - update
- [ ] POST /orders/{id}/confirm - save confirmation
- [ ] POST /orders/{id}/cancel - cancel order
- [ ] POST /orders/{id}/complete - mark complete

**🔧 Confirm Endpoint:**
```php
POST /api/v1/admin/orders/{id}/confirm
{
    "confirmed_address": "...",
    "entrance": "2",
    "floor": "3",
    "apartment": "45",
    "landmark": "...",
    "has_pi_pi": false,
    "call_outcome": "confirmed"
}
```

**⏱ Estimate:** 3h | **🏷 Labels:** `backend`, `api`, `P0`

---

## GT-035: Payme Service

**📝 Description:**
Payme to'lov integratsiyasi - service class.

**✅ Acceptance Criteria:**
- [ ] PaymeService class
- [ ] Create invoice (checkout URL)
- [ ] Signature generation
- [ ] Amount in tiyin (x100)

**🔧 Service:**
```php
class PaymeService
{
    public function createInvoice(Order $order): string
    {
        $params = [
            'm' => config('services.payme.merchant_id'),
            'ac.order_id' => $order->id,
            'a' => $order->price * 100, // tiyin
            'c' => route('payme.return')
        ];
        
        $encoded = base64_encode(http_build_query($params));
        return "https://checkout.paycom.uz/{$encoded}";
    }
}
```

**⏱ Estimate:** 2h | **🏷 Labels:** `backend`, `payments`, `P0`

---

## GT-036: Payme Webhook

**📝 Description:**
Payme webhook handler.

**✅ Acceptance Criteria:**
- [ ] POST /api/webhooks/payme
- [ ] Signature verification
- [ ] CheckPerformTransaction
- [ ] CreateTransaction
- [ ] PerformTransaction
- [ ] CancelTransaction
- [ ] Update payment & order status
- [ ] Trigger PAID notification

**🔧 Methods:**
```php
class PaymeWebhookController
{
    public function handle(Request $request)
    {
        $this->verifySignature($request);
        
        $method = $request->input('method');
        
        return match($method) {
            'CheckPerformTransaction' => $this->checkPerform($request),
            'CreateTransaction' => $this->createTransaction($request),
            'PerformTransaction' => $this->performTransaction($request),
            'CancelTransaction' => $this->cancelTransaction($request),
        };
    }
    
    private function performTransaction($request)
    {
        // Find payment, update status
        // Update order status to PAID
        // Trigger PAID notification
    }
}
```

**⏱ Estimate:** 4h | **🏷 Labels:** `backend`, `payments`, `P0`

---

## GT-037: Click Service

**📝 Description:**
Click to'lov integratsiyasi - service class.

**✅ Acceptance Criteria:**
- [ ] ClickService class
- [ ] Create payment URL
- [ ] Signature generation

**🔧 Service:**
```php
class ClickService
{
    public function createInvoice(Order $order): string
    {
        $params = [
            'merchant_id' => config('services.click.merchant_id'),
            'service_id' => config('services.click.service_id'),
            'amount' => $order->price,
            'transaction_param' => $order->id,
            'return_url' => route('click.return')
        ];
        
        return "https://my.click.uz/services/pay?" . http_build_query($params);
    }
}
```

**⏱ Estimate:** 1.5h | **🏷 Labels:** `backend`, `payments`, `P0`

---

## GT-038: Click Webhook

**📝 Description:**
Click webhook handler.

**✅ Acceptance Criteria:**
- [ ] POST /api/webhooks/click
- [ ] Signature verification
- [ ] Prepare action (action=0)
- [ ] Complete action (action=1)
- [ ] Update payment & order status
- [ ] Trigger PAID notification

**🔧 Handler:**
```php
class ClickWebhookController
{
    public function handle(Request $request)
    {
        $this->verifySignature($request);
        
        $action = $request->input('action');
        
        if ($action == 0) {
            return $this->prepare($request);
        }
        
        if ($action == 1) {
            return $this->complete($request);
        }
    }
}
```

**⏱ Estimate:** 3h | **🏷 Labels:** `backend`, `payments`, `P0`

---

## GT-039: PAID Notification

**📝 Description:**
To'lov qabul qilinganda Telegram xabar.

**✅ Acceptance Criteria:**
- [ ] Trigger on payment webhook (status=PAID)
- [ ] Send to OPS group
- [ ] Include: order number, amount, provider
- [ ] Queue job

**🔧 Message:**
```
💰 TO'LOV QABUL QILINDI

📋 GT-20250210-001
💳 Payme: 250,000 so'm
✅ Status: PAID

🔗 Admin: https://...
```

**⏱ Estimate:** 1h | **🏷 Labels:** `backend`, `telegram`, `P1`

---

## GT-040: READY Notification

**📝 Description:**
Usta uchun tayyor xabar (RESERVED bo'lganda).

**✅ Acceptance Criteria:**
- [ ] Trigger when: Confirmed + Paid + Reserved
- [ ] Send to Therapist group
- [ ] Full work order info
- [ ] Links: master_day_link, order_link
- [ ] Duplicate prevention

**🔧 Message:**
```
📋 YANGI BUYURTMA TAYYOR

🧑‍⚕️ Master: Anvar
📅 10-Fev, 10:00

👤 Mijoz: Ali
📞 +998901234567
📍 Chilanzar, 5-dom, 2-kirish, 3-qavat, 45-xonadon
🗺 Mo'ljal: Metro yonida

💆 Relax yog'li massaj (Coconut)
📝 Izoh: ...

🔗 Kun: /m/abc123/day/2025-02-10
🔗 Buyurtma: /o/xyz789
```

**⏱ Estimate:** 2h | **🏷 Labels:** `backend`, `telegram`, `P1`

---

## GT-041: Masters CRUD

**📝 Description:**
Admin panelda ustalar boshqaruvi.

**✅ Acceptance Criteria:**
- [ ] Masters list table
- [ ] Add master form (name, phone, bio, photo upload)
- [ ] Edit master
- [ ] Toggle active/inactive
- [ ] Regenerate token button

**⏱ Estimate:** 3h | **🏷 Labels:** `frontend`, `admin`, `P1`

---

## GT-042: Slots Management

**📝 Description:**
Admin panelda slotlar boshqaruvi.

**✅ Acceptance Criteria:**
- [ ] Calendar view (by master, by date)
- [ ] Bulk slot generation (date range, time range)
- [ ] Block/unblock individual slots
- [ ] View slot status

**🔧 Generate Slots:**
```php
POST /api/v1/admin/slots/generate
{
    "master_id": 1,
    "date_from": "2025-02-10",
    "date_to": "2025-02-16",
    "start_time": "09:00",
    "end_time": "18:00",
    "duration": 60,
    "gap": 30
}
```

**⏱ Estimate:** 3h | **🏷 Labels:** `frontend`, `admin`, `P1`

---

## 📊 Summary

| ID | Task | Hours |
|----|------|-------|
| GT-029 | Admin Login | 1.5h |
| GT-030 | Admin Layout | 2h |
| GT-031 | Orders List | 4h |
| GT-032 | Order Card (A-D) | 4h |
| GT-033 | Order Card (E-G) | 3h |
| GT-034 | Order Admin API | 3h |
| GT-035 | Payme Service | 2h |
| GT-036 | Payme Webhook | 4h |
| GT-037 | Click Service | 1.5h |
| GT-038 | Click Webhook | 3h |
| GT-039 | PAID Notification | 1h |
| GT-040 | READY Notification | 2h |
| GT-041 | Masters CRUD | 3h |
| GT-042 | Slots Management | 3h |

**Total: ~22h + 3h buffer = ~25h**
