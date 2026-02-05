# 📋 Sprint 4: Customer Portal + QA - Trello Tasks

## Sprint Info
- **Sprint:** 4
- **Focus:** Customer Portal + Master Pages + QA
- **Dates:** 24 Fevral - 2 Mart 2025
- **Delivery:** 2-Mart (Yakshanba)
- **Hours:** ~22h work + ~3h buffer

---

## GT-043: Eskiz OTP Service

**📝 Description:**
Eskiz SMS orqali OTP yuborish.

**✅ Acceptance Criteria:**
- [ ] EskizService class
- [ ] Auth token olish va cache qilish
- [ ] Send SMS method
- [ ] OTP generate (6 digit)
- [ ] OTP store with expiry (2 min)
- [ ] Rate limiting (5 attempts per 15 min)

**⏱ Estimate:** 3h | **🏷 Labels:** `backend`, `sms`, `P0`

---

## GT-044: Customer OTP Login - Backend

**📝 Description:**
Mijoz OTP login API endpoints.

**✅ Acceptance Criteria:**
- [ ] POST /api/v1/auth/otp/send - send OTP
- [ ] POST /api/v1/auth/otp/verify - verify & login
- [ ] Create customer if not exists
- [ ] Return Sanctum token
- [ ] Rate limiting response

**⏱ Estimate:** 2h | **🏷 Labels:** `backend`, `auth`, `P0`

---

## GT-045: Customer OTP Login - Frontend

**📝 Description:**
Mijoz login UI.

**✅ Acceptance Criteria:**
- [ ] Phone input page
- [ ] OTP input page (6 digits)
- [ ] Countdown timer (resend after 60s)
- [ ] Resend button
- [ ] Error handling
- [ ] Store token, redirect

**⏱ Estimate:** 3h | **🏷 Labels:** `frontend`, `auth`, `P0`

---

## GT-046: Customer Orders List

**📝 Description:**
Mijoz buyurtmalari ro'yxati.

**✅ Acceptance Criteria:**
- [ ] GET /api/v1/customer/orders
- [ ] List view (order cards)
- [ ] Status badge
- [ ] Empty state

**⏱ Estimate:** 3h | **🏷 Labels:** `frontend`, `customer`, `P0`

---

## GT-047: Customer Order Detail

**📝 Description:**
Mijoz buyurtma tafsilotlari.

**✅ Acceptance Criteria:**
- [ ] Full order info
- [ ] Status timeline
- [ ] Pay button (if WAITING_PAYMENT)
- [ ] Cancel request button

**⏱ Estimate:** 2.5h | **🏷 Labels:** `frontend`, `customer`, `P0`

---

## GT-048: Customer Profile

**📝 Description:**
Mijoz profili sahifasi.

**✅ Acceptance Criteria:**
- [ ] View/Edit name
- [ ] Phone (read-only)
- [ ] Orders count
- [ ] Logout button

**⏱ Estimate:** 1.5h | **🏷 Labels:** `frontend`, `customer`, `P1`

---

## GT-049: Customer Addresses CRUD

**📝 Description:**
Mijoz manzillari boshqaruvi.

**✅ Acceptance Criteria:**
- [ ] Addresses list
- [ ] Add/Edit/Delete address
- [ ] Set default address

**⏱ Estimate:** 3h | **🏷 Labels:** `frontend`, `customer`, `P1`

---

## GT-050: Cancel Request

**📝 Description:**
Mijoz buyurtmani bekor qilish so'rovi.

**✅ Acceptance Criteria:**
- [ ] Cancel button (if status allows)
- [ ] Confirmation modal
- [ ] Update status to CANCEL_REQUESTED
- [ ] Notify admin

**⏱ Estimate:** 1.5h | **🏷 Labels:** `backend`, `customer`, `P1`

---

## GT-051: Master Day View Page

**📝 Description:**
Usta kunlik buyurtmalar sahifasi.

**✅ Acceptance Criteria:**
- [ ] Public URL: /m/{token}/day/{date}
- [ ] No auth required (token-based)
- [ ] List of RESERVED orders
- [ ] Date navigation

**⏱ Estimate:** 3h | **🏷 Labels:** `frontend`, `master`, `P0`

---

## GT-052: Master Order Detail Page

**📝 Description:**
Usta uchun buyurtma tafsilotlari.

**✅ Acceptance Criteria:**
- [ ] Public URL: /o/{token}
- [ ] Full order info
- [ ] Customer phone (clickable)
- [ ] Full address with map link

**⏱ Estimate:** 2h | **🏷 Labels:** `frontend`, `master`, `P0`

---

## GT-053: Master Pages API

**📝 Description:**
Master public pages uchun API.

**✅ Acceptance Criteria:**
- [ ] GET /api/v1/m/{token}/day/{date}
- [ ] GET /api/v1/o/{token}
- [ ] Token validation
- [ ] Security filters

**⏱ Estimate:** 2h | **🏷 Labels:** `backend`, `api`, `P0`

---

## GT-054: QA Form Component

**📝 Description:**
Sifat nazorati formasi.

**✅ Acceptance Criteria:**
- [ ] Overall rating (1-5 stars)
- [ ] Punctuality, Professionalism ratings
- [ ] Yes/No checkboxes
- [ ] Hygiene issue (with required comment)
- [ ] Client feedback textarea

**⏱ Estimate:** 2.5h | **🏷 Labels:** `frontend`, `admin`, `P1`

---

## GT-055: Complete Order Flow

**📝 Description:**
Buyurtmani yakunlash logic.

**✅ Acceptance Criteria:**
- [ ] QA form required before complete
- [ ] POST /api/v1/admin/orders/{id}/complete
- [ ] Update status to COMPLETED
- [ ] Store QA data

**⏱ Estimate:** 2h | **🏷 Labels:** `backend`, `admin`, `P1`

---

## GT-056: CSV Export

**📝 Description:**
Buyurtmalar CSV export.

**✅ Acceptance Criteria:**
- [ ] GET /api/v1/admin/export/orders
- [ ] Date range filter
- [ ] UTF-8 BOM encoding
- [ ] Download button

**⏱ Estimate:** 2h | **🏷 Labels:** `backend`, `export`, `P1`

---

## GT-057: Audit Logs

**📝 Description:**
Audit logging implementatsiyasi.

**✅ Acceptance Criteria:**
- [ ] Log order status changes
- [ ] Log payment events
- [ ] Log admin actions
- [ ] Store user, IP, timestamp

**⏱ Estimate:** 2h | **🏷 Labels:** `backend`, `audit`, `P2`

---

## 📊 Summary

| ID | Task | Hours |
|----|------|-------|
| GT-043 | Eskiz OTP Service | 3h |
| GT-044 | OTP Login Backend | 2h |
| GT-045 | OTP Login Frontend | 3h |
| GT-046 | Customer Orders List | 3h |
| GT-047 | Customer Order Detail | 2.5h |
| GT-048 | Customer Profile | 1.5h |
| GT-049 | Customer Addresses | 3h |
| GT-050 | Cancel Request | 1.5h |
| GT-051 | Master Day View | 3h |
| GT-052 | Master Order Detail | 2h |
| GT-053 | Master Pages API | 2h |
| GT-054 | QA Form Component | 2.5h |
| GT-055 | Complete Order Flow | 2h |
| GT-056 | CSV Export | 2h |
| GT-057 | Audit Logs | 2h |

**Total: ~22h + 3h buffer = ~25h**
