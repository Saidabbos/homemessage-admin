# 📋 Sabai - Qolgan Ishlar (TZ v2.0 asosida)

**Sana:** 2026-02-25 (yangilangan)
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

### Payme/Click Integration
- [x] GT-060 — Payme Integration (PaymentService, config, routes)
- [x] GT-061 — Payme Webhook (PaymeController, JSON-RPC methods, mock testing)
- [x] GT-062 — Click Integration (PaymentService, config)
- [x] GT-063 — Click Webhook (ClickController, prepare/complete)

### Customer Dashboard
- [x] GT-065 — Customer Orders List (OrderController, Index.vue, filters)
- [x] GT-066 — Customer Order Detail (Show.vue, status timeline, pay buttons)
- [x] GT-069 — Customer Profile (ProfileController, Edit.vue, PIN management)
- [x] GT-070 — Customer Addresses CRUD (AddressController, model, migrations, Vue pages)
- [x] Customer Masters List + Detail (MasterController, Masters/Index.vue, Masters/Show.vue)

### Admin Features
- [x] GT-071 — CSV Export (ReportController::export(), date range, UTF-8 BOM)
- [x] GT-072 — Audit Log History (audit_logs table, model, admin UI, filters)
- [x] GT-073 — Reschedule Order (OrderService::reschedule(), admin modal, slot swap)

---

## ⚠️ QISMAN BAJARILGAN (qolgan kichik ishlar)

### GT-059: Eskiz SMS Moderatsiya
**TZ Ref:** M7-DEV-CAB-002
**Priority:** P0 | **Status:** ⏳ Kutilmoqda

**Bajarilgan:**
- [x] SmsService class yaratilgan
- [x] 3 template (uz, ru, en) buildMessage() ichida
- [x] Config (eskiz email, password, sender, base_url, skip_send)

**Qolgan:**
- [ ] my.eskiz.uz → SMS → Мои тексты da moderatsiya uchun template yuborish
- [ ] Moderatsiya kutish (1-24 soat)
- [ ] `ESKIZ_SKIP_SEND=false` qaytarish

---

### GT-064: READY Notification — "Resend" tugmasi
**TZ Ref:** M7-TG-CH002
**Priority:** P0 | **Status:** ⚠️ Kichik ish qolgan

**Bajarilgan:**
- [x] THERAPIST group notification (notifyReady())
- [x] READY message template
- [x] ready_sent_at tracking
- [x] Duplicate prevention
- [x] master_day_link, public_order_link

**Qolgan:**
- [ ] "Resend READY" tugmasi admin Order Show sahifasida
- [ ] Route: `POST admin/orders/{order}/resend-ready`

---

### GT-067: Cancel Request — CANCEL_REQUESTED status
**TZ Ref:** M7-CAB007
**Priority:** P1 | **Status:** ⚠️ Kichik ish qolgan

**Bajarilgan:**
- [x] Cancel button (customer Orders/Show.vue)
- [x] Cancel → CANCELLED + slot FREE
- [x] Confirmation modal

**Qolgan:**
- [ ] CANCEL_REQUESTED intermediate status (RESERVED dan keyin)
- [ ] Admin notification for cancel request
- [ ] Admin approve/reject cancel

---

### GT-068: Payment Logs Admin
**TZ Ref:** M7-DEV-PAY-005
**Priority:** P1 | **Status:** ⚠️ Kichik ish qolgan

**Bajarilgan:**
- [x] payments table (transaction history per order)
- [x] Payment history ko'rinishi admin Order Show ichida

**Qolgan:**
- [ ] Alohida payment_logs admin sahifasi (barcha webhook eventlar)
- [ ] Filter by status/provider/date range
- [ ] Route: `GET /admin/payment-logs`

---

## 📊 Summary

| Status | Count | Izoh |
|--------|-------|------|
| **✅ Bajarilgan** | 11 task | To'liq tayyor |
| **⚠️ Qisman** | 4 task | Kichik ishlar qolgan |
| **Jami** | **15 task** | |

---

## 🎯 Qolgan ishlar (tartib bo'yicha)

### Kod yozish kerak:
1. **GT-064** — "Resend READY" tugmasi (admin) — ~30 min
2. **GT-067** — CANCEL_REQUESTED status qo'shish — ~1h
3. **GT-068** — Payment Logs admin sahifasi — ~1.5h

### Tashqi xizmatlar (kod yozish shart emas):
4. **GT-059** — Eskiz moderatsiya (my.eskiz.uz dan template yuborish, keyin .env da `ESKIZ_SKIP_SEND=false`)

---

## 📝 Config Kerak

```env
# Eskiz (hozir skip mode)
ESKIZ_SKIP_SEND=true  # → false keyin

# Payme (sozlangan - test mode)
PAYME_ENABLED=true
PAYME_MERCHANT_ID=test_merchant
PAYME_KEY=test_key
PAYME_TEST_MODE=true

# Click (sozlangan - test mode)
CLICK_ENABLED=true
CLICK_MERCHANT_ID=test_merchant
CLICK_SERVICE_ID=12345
CLICK_SECRET_KEY=test_secret
CLICK_TEST_MODE=true
```
