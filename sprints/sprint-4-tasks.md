# 📋 Sprint 4: Customer Portal + QA - Trello Tasks

## Sprint Info
- **Sprint:** 4
- **Focus:** Customer Portal + Master Pages + QA
- **Dates:** 24 Fevral - 2 Mart 2025
- **Delivery:** 2-Mart (Yakshanba)
- **Hours:** ~22h work + ~3h buffer
- **Status:** ✅ BAJARILGAN

---

## GT-043: Eskiz OTP Service ✅

**✅ Acceptance Criteria:**
- [x] SmsService class (EskizService)
- [x] Auth token olish va cache qilish
- [x] Send SMS method
- [x] OTP generate (6 digit)
- [x] OTP store with expiry (2 min)
- [x] Rate limiting (5 attempts per 15 min)

**⏱ Estimate:** 3h | **🏷 Labels:** `backend`, `sms`, `P0`

---

## GT-044: Customer OTP Login - Backend ✅

**✅ Acceptance Criteria:**
- [x] POST /api/v1/auth/otp/send - send OTP
- [x] POST /api/v1/auth/otp/verify - verify & login
- [x] Create customer if not exists
- [x] Return session/token
- [x] Rate limiting response

**⏱ Estimate:** 2h | **🏷 Labels:** `backend`, `auth`, `P0`

---

## GT-045: Customer OTP Login - Frontend ✅

**✅ Acceptance Criteria:**
- [x] Phone input page
- [x] OTP input page (6 digits)
- [x] Countdown timer (resend after 60s)
- [x] Resend button
- [x] Error handling
- [x] Store token, redirect

**⏱ Estimate:** 3h | **🏷 Labels:** `frontend`, `auth`, `P0`

---

## GT-046: Customer Orders List ✅

**✅ Acceptance Criteria:**
- [x] GET /customer/orders
- [x] List view (order cards)
- [x] Status badge
- [x] Empty state
- [x] Filter by status, search

**⏱ Estimate:** 3h | **🏷 Labels:** `frontend`, `customer`, `P0`

---

## GT-047: Customer Order Detail ✅

**✅ Acceptance Criteria:**
- [x] Full order info
- [x] Status timeline
- [x] Pay button (if WAITING_PAYMENT) — Payme + Click
- [x] Cancel request button

**⏱ Estimate:** 2.5h | **🏷 Labels:** `frontend`, `customer`, `P0`

---

## GT-048: Customer Profile ✅

**✅ Acceptance Criteria:**
- [x] View/Edit name
- [x] Phone (read-only)
- [x] PIN management (set/remove)
- [x] Logout button

**⏱ Estimate:** 1.5h | **🏷 Labels:** `frontend`, `customer`, `P1`

---

## GT-049: Customer Addresses CRUD ✅

**✅ Acceptance Criteria:**
- [x] Addresses list
- [x] Add/Edit/Delete address
- [x] Set default address
- [x] Coordinates support

**⏱ Estimate:** 3h | **🏷 Labels:** `frontend`, `customer`, `P1`

---

## GT-050: Cancel Request ✅ (CANCEL_REQUESTED status qolgan)

**✅ Acceptance Criteria:**
- [x] Cancel button (if status allows)
- [x] Confirmation modal
- [x] Cancel → CANCELLED + slot FREE
- [ ] CANCEL_REQUESTED intermediate status (qolgan)
- [ ] Admin notification for cancel request (qolgan)

**⏱ Estimate:** 1.5h | **🏷 Labels:** `backend`, `customer`, `P1`

---

## GT-051: Master Day View Page ✅

**✅ Acceptance Criteria:**
- [x] Public URL: /m/{token}/day/{date}
- [x] No auth required (token-based)
- [x] List of RESERVED orders
- [x] Date navigation

**⏱ Estimate:** 3h | **🏷 Labels:** `frontend`, `master`, `P0`

---

## GT-052: Master Order Detail Page ✅

**✅ Acceptance Criteria:**
- [x] Public URL: /o/{token}
- [x] Full order info
- [x] Customer phone (clickable)
- [x] Full address with map link

**⏱ Estimate:** 2h | **🏷 Labels:** `frontend`, `master`, `P0`

---

## GT-053: Master Pages API ✅

**✅ Acceptance Criteria:**
- [x] GET /api/v1/m/{token}/day/{date}
- [x] GET /api/v1/o/{token}
- [x] Token validation
- [x] Security filters

**⏱ Estimate:** 2h | **🏷 Labels:** `backend`, `api`, `P0`

---

## GT-054: QA Form Component ✅

**✅ Acceptance Criteria:**
- [x] Overall rating (1-5 stars)
- [x] Punctuality, Professionalism ratings
- [x] Yes/No checkboxes
- [x] Hygiene issue (with required comment)
- [x] Client feedback textarea

**⏱ Estimate:** 2.5h | **🏷 Labels:** `frontend`, `admin`, `P1`

---

## GT-055: Complete Order Flow ✅

**✅ Acceptance Criteria:**
- [x] QA form required before complete
- [x] POST /admin/orders/{id}/complete
- [x] Update status to COMPLETED
- [x] Store QA data

**⏱ Estimate:** 2h | **🏷 Labels:** `backend`, `admin`, `P1`

---

## GT-056: CSV Export ✅

**✅ Acceptance Criteria:**
- [x] GET /admin/reports/export
- [x] Date range filter
- [x] UTF-8 BOM encoding
- [x] Download button

**⏱ Estimate:** 2h | **🏷 Labels:** `backend`, `export`, `P1`

---

## GT-057: Audit Logs ✅

**✅ Acceptance Criteria:**
- [x] Log order status changes
- [x] Log payment events
- [x] Log admin actions
- [x] Store user, IP, timestamp
- [x] Admin UI with filters

**⏱ Estimate:** 2h | **🏷 Labels:** `backend`, `audit`, `P2`

---

## 📊 Summary

| ID | Task | Hours | Status |
|----|------|-------|--------|
| GT-043 | Eskiz OTP Service | 3h | ✅ |
| GT-044 | OTP Login Backend | 2h | ✅ |
| GT-045 | OTP Login Frontend | 3h | ✅ |
| GT-046 | Customer Orders List | 3h | ✅ |
| GT-047 | Customer Order Detail | 2.5h | ✅ |
| GT-048 | Customer Profile | 1.5h | ✅ |
| GT-049 | Customer Addresses | 3h | ✅ |
| GT-050 | Cancel Request | 1.5h | ✅ (CANCEL_REQUESTED qolgan) |
| GT-051 | Master Day View | 3h | ✅ |
| GT-052 | Master Order Detail | 2h | ✅ |
| GT-053 | Master Pages API | 2h | ✅ |
| GT-054 | QA Form Component | 2.5h | ✅ |
| GT-055 | Complete Order Flow | 2h | ✅ |
| GT-056 | CSV Export | 2h | ✅ |
| GT-057 | Audit Logs | 2h | ✅ |

**Total: ~22h — ALL DONE ✅** (GT-050 CANCEL_REQUESTED qolgan)
