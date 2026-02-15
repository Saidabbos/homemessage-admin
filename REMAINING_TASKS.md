# 📋 HomeMessage - Qolgan Ishlar (TZ v2.0 asosida)

**Sana:** 2026-02-14
**TZ Versiya:** 2.0 (Customer Portal + OTP, Payme/Click API+Webhooks)

---

## ✅ BAJARILGAN

### Publik Sayt (M7-PUB-*)
- [x] M7-PUB-UI001 — iPhone look & feel (premium minimal)
- [x] M7-PUB-UI002 — Fixed bottom bar (Total + Next)
- [x] M7-PUB-FLW001-008 — Booking flow (type → master → date → slot → form)
- [x] M7-PUB-SLOT001-002 — Slot statuses + double booking protection
- [x] M7-PUB-ORD001-002 — Order creation + slot PENDING
- [x] M7-PUB-RULE001-002 — 6-hour lead time rule

### Telegram (M7-TG-*)
- [x] M7-TG-CH001 — OPS group (#1) notifications
- [x] M7-TGB001 — NEW notification
- [x] M7-TGB002 — PAID notification
- [x] M7-TG-EVT001-002 — NEW + PAID events

### Admin Panel (M7-ADM-*, M7-ORD-*)
- [x] M7-ADN001-003 — Auth, hashing, sessions
- [x] M7-ORD001-004 — Orders table + filters + search
- [x] M7-VER001-004 — Confirmation form (Block C)
- [x] M7-RES001-003 — Reservation (Block E)
- [x] M7-WO-DIG001-005 — Digital work order (Block F)
- [x] M7-QA001-005 — QA form (Block G)

### Master Pages (M7-WEB-MAS-*)
- [x] M7-WEB-MAS001-002 — Master Day View
- [x] M7-WEB-ORDP001 — Public Order View
- [x] M7-SEC-PUB001-003 — Token security

### OTP/SMS
- [x] M7-CAB001 — OTP login (phone + PIN)
- [x] M7-CAB002 — OTP security (TTL, rate limit)
- [x] M7-CAB003 — Sessions

---

## 🔴 QOLGAN - P0 (Critical)

### GT-059: Eskiz SMS Moderatsiya
**TZ Ref:** M7-DEV-CAB-002
**Priority:** P0 | **Estimate:** 1h | **Status:** ⏳ Blocked

**Tasks:**
- [ ] my.eskiz.uz → SMS → Мои тексты
- [ ] 3 template qo'shish (uz, ru, en)
- [ ] Moderatsiya kutish (1-24 soat)
- [ ] `ESKIZ_SKIP_SEND=false` qaytarish

---

### GT-060: Payme Integration
**TZ Ref:** M7-PAY001-006, M7-DEV-PAY-001
**Priority:** P0 | **Estimate:** 6h | **Status:** 🚫 Blocked (Merchant ID kerak)

**Tasks:**
- [ ] PaymeService class
- [ ] Create invoice/checkout URL
- [ ] Signature generation
- [ ] Config: `PAYME_MERCHANT_ID`, `PAYME_SECRET_KEY`

---

### GT-061: Payme Webhook
**TZ Ref:** M7-PAY003-006, M7-DEV-PAY-003
**Priority:** P0 | **Estimate:** 4h | **Status:** 🚫 Blocked

**Tasks:**
- [ ] POST `/api/webhooks/payme`
- [ ] Signature verification
- [ ] CheckPerformTransaction
- [ ] CreateTransaction
- [ ] PerformTransaction
- [ ] CancelTransaction
- [ ] Auto: payment_status → PAID
- [ ] Auto: slot → RESERVED (agar mumkin)
- [ ] Idempotency (duplicate protection)
- [ ] Logging

---

### GT-062: Click Integration
**TZ Ref:** M7-DEV-PAY-002
**Priority:** P0 | **Estimate:** 4h | **Status:** 🚫 Blocked

**Tasks:**
- [ ] ClickService class
- [ ] Create invoice URL
- [ ] Config: `CLICK_MERCHANT_ID`, `CLICK_SERVICE_ID`

---

### GT-063: Click Webhook
**TZ Ref:** M7-DEV-PAY-003
**Priority:** P0 | **Estimate:** 3h | **Status:** 🚫 Blocked

**Tasks:**
- [ ] POST `/api/webhooks/click`
- [ ] Signature verification
- [ ] Prepare (action=0)
- [ ] Complete (action=1)
- [ ] Auto status update

---

### GT-064: READY Notification (THERAPISTS Group)
**TZ Ref:** M7-TG-CH002, M7-TG-EVT003-004, M7-TPL-TG-TH-READY001
**Priority:** P0 | **Estimate:** 2h | **Status:** 🚫 Blocked (Group ID kerak)

**Trigger:** call_outcome=Confirmed + payment_status=PAID + slot_status=RESERVED

**Tasks:**
- [ ] THERAPIST_BOT_TOKEN config
- [ ] THERAPIST_GROUP_CHAT_ID config
- [ ] READY message template (full order details)
- [ ] Include links: master_day_link, public_order_link
- [ ] Duplicate prevention (ready_sent_at)
- [ ] "Resend READY" button in admin

**Message Template:**
```
READY ✅ | #{{order_id}}
Master: {{therapist_name}}
Time: {{date}} {{time}} (60 min)
Massage: {{massage_type}}{{oil_short}}

Client
Phone: {{client_phone}}
Name: {{client_name}}
On-site: {{onsite_phone}}

Address
{{address}}
Entrance: {{entrance}} · Floor: {{floor}} · Elevator: {{elevator}}
Parking: {{parking}}
Landmark: {{landmark}}

Notes
Constraints: {{constraints}}
Note to therapist: {{note_to_therapist}}
Space 2×2: {{space_ok}} · Pets: {{pets}}

Payment
PAID ✅ · {{amount}} UZS · {{payment_method}}

Links:
My day: {{master_day_link}}
Order: {{public_order_link}}
```

---

## 🟡 QOLGAN - P1 (Important)

### GT-065: Customer Orders List
**TZ Ref:** M7-CAB005
**Priority:** P1 | **Estimate:** 3h

**Tasks:**
- [ ] GET `/api/v1/customer/orders`
- [ ] Orders list UI (TMA/Web)
- [ ] Status badges
- [ ] Sort by date, filter by status
- [ ] "Повторить заказ" button (pre-fill)

---

### GT-066: Customer Order Detail
**TZ Ref:** M7-CAB005-006
**Priority:** P1 | **Estimate:** 2.5h

**Tasks:**
- [ ] Order detail page
- [ ] Status timeline
- [ ] "Оплатить" button (if WAITING_PAYMENT)
- [ ] Payment URL redirect
- [ ] Auto-update after webhook (polling/websocket)

---

### GT-067: Cancel Request
**TZ Ref:** M7-CAB007
**Priority:** P1 | **Estimate:** 1.5h

**Tasks:**
- [ ] Cancel button (before RESERVED)
- [ ] Cancel → slot FREE
- [ ] Request cancel (after RESERVED) → CANCEL_REQUESTED
- [ ] Admin notification

---

### GT-068: Payment Logs Admin
**TZ Ref:** M7-DEV-PAY-005
**Priority:** P1 | **Estimate:** 2h

**Tasks:**
- [ ] payment_logs table
- [ ] Log all webhook events
- [ ] Admin UI: payments log table
- [ ] Filter by status/provider/date

---

## 🟢 QOLGAN - P2 (Nice to Have)

### GT-069: Customer Profile
**TZ Ref:** M7-CAB004
**Priority:** P2 | **Estimate:** 1.5h

**Tasks:**
- [ ] Name (editable)
- [ ] Phone (read-only)
- [ ] Logout button

---

### GT-070: Customer Addresses CRUD
**TZ Ref:** M7-CAB004
**Priority:** P2 | **Estimate:** 3h

**Tasks:**
- [ ] Addresses list
- [ ] Add/Edit/Delete
- [ ] Set default address
- [ ] Pre-fill in booking

---

### GT-071: CSV Export
**TZ Ref:** M7-DAT001-002, M7-DEV-EXP-001
**Priority:** P2 | **Estimate:** 2h

**Tasks:**
- [ ] Date range selector
- [ ] Export button
- [ ] Include: order + confirmation + payment + QA
- [ ] UTF-8 BOM encoding

---

### GT-072: Audit Log History
**TZ Ref:** M7-AUD001, M7-DEV-AUD-001
**Priority:** P2 | **Estimate:** 2h

**Tasks:**
- [ ] audit_log table
- [ ] Log: created, paid, reserved, cancelled, completed, ready_sent
- [ ] Event history in order card
- [ ] Who/when/what

---

### GT-073: Reschedule Order
**TZ Ref:** Block B in TZ section 8.3
**Priority:** P2 | **Estimate:** 2h

**Tasks:**
- [ ] "Перенести слот" button
- [ ] Select new FREE slot
- [ ] Old slot → FREE
- [ ] New slot → PENDING
- [ ] Log reschedule event

---

## 📊 Summary

| Priority | Count | Hours | Status |
|----------|-------|-------|--------|
| **P0** | 6 | 20h | 🚫 5 blocked, ⏳ 1 waiting |
| **P1** | 4 | 9h | ✅ Ready |
| **P2** | 5 | 10.5h | ✅ Ready |
| **Total** | **15** | **39.5h** | |

---

## 🚫 BLOCKED ITEMS

| Task | Blocking Reason | Action Needed |
|------|-----------------|---------------|
| GT-059 | Eskiz moderatsiya | Template yuborish va kutish |
| GT-060-063 | Payme/Click | Merchant ID olish |
| GT-064 | THERAPISTS group | Group yaratish + Bot qo'shish |

---

## 🎯 Keyingi Qadamlar (tartib bo'yicha)

### Bugun qilish mumkin:
1. **GT-065-066** — Customer orders list/detail
2. **GT-067** — Cancel request
3. **GT-071** — CSV export

### Blocked items uchun kerak:
1. **Eskiz** — my.eskiz.uz → SMS template yuborish
2. **Payme** — Merchant ID + Secret key olish
3. **Click** — Merchant ID + Service ID olish
4. **Telegram** — THERAPISTS group yaratish, @h_m_UZ_bot ni qo'shish

---

## 📝 Config Kerak

```env
# Eskiz (hozir skip mode)
ESKIZ_SKIP_SEND=true  # → false keyin

# Payme (kerak)
PAYME_MERCHANT_ID=
PAYME_SECRET_KEY=
PAYME_TEST_MODE=true

# Click (kerak)
CLICK_MERCHANT_ID=
CLICK_SERVICE_ID=
CLICK_SECRET_KEY=

# Telegram THERAPISTS (kerak)
THERAPIST_BOT_TOKEN=
THERAPIST_GROUP_CHAT_ID=
```
