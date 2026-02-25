# 📋 Sprint 3: Admin + Payments - Trello Tasks

## Sprint Info
- **Sprint:** 3
- **Focus:** Admin Panel + Payment Integrations
- **Dates:** 17-23 Fevral 2025
- **Delivery:** 23-Fevral (Yakshanba)
- **Hours:** ~22h work + ~3h buffer
- **Status:** ✅ BAJARILGAN

---

## GT-029: Admin Login Page ✅

**✅ Acceptance Criteria:**
- [x] Email input
- [x] Password input
- [x] Login button with loading
- [x] Error message display
- [x] Redirect to dashboard on success
- [x] Store token in localStorage

**⏱ Estimate:** 1.5h | **🏷 Labels:** `frontend`, `admin`, `P0`

---

## GT-030: Admin Layout ✅

**✅ Acceptance Criteria:**
- [x] Sidebar navigation (Orders, Masters, Slots, Settings)
- [x] Header (user info, logout)
- [x] Main content area
- [x] Mobile responsive (collapsible sidebar)
- [x] Active route highlight

**⏱ Estimate:** 2h | **🏷 Labels:** `frontend`, `admin`, `P0`

---

## GT-031: Orders List Page ✅

**✅ Acceptance Criteria:**
- [x] Table view (order_number, customer, master, date, status, actions)
- [x] Status badges with colors
- [x] Filters: status, master, date range
- [x] Search by phone/order number
- [x] Pagination
- [x] Click row → Order detail
- [x] Loading state

**⏱ Estimate:** 4h | **🏷 Labels:** `frontend`, `admin`, `P0`

---

## GT-032: Order Card Page - Part 1 ✅

**✅ Acceptance Criteria:**
- [x] **Block A - Client Info:** phone, name, orders count
- [x] **Block B - Order Details:** order_number, massage_type, price, date/time, master
- [x] **Block C - Confirmation Form:** address inputs, pi_pi checkbox, call outcome
- [x] **Block D - Payment Section:** payment status, create invoice button, payment link

**⏱ Estimate:** 4h | **🏷 Labels:** `frontend`, `admin`, `P0`

---

## GT-033: Order Card Page - Part 2 ✅

**✅ Acceptance Criteria:**
- [x] **Block E - Reservation:** slot status, reserve/unreserve buttons
- [x] **Block F - Digital Work Order:** summary for master (read-only)
- [x] **Block G - QA Section:** rating form (after completion)
- [x] Action buttons: Confirm, Cancel, Complete
- [x] Status change confirmation modals

**⏱ Estimate:** 3h | **🏷 Labels:** `frontend`, `admin`, `P0`

---

## GT-034: Order Admin API ✅

**✅ Acceptance Criteria:**
- [x] GET /orders - list with filters
- [x] GET /orders/{id} - full details
- [x] PUT /orders/{id} - update
- [x] POST /orders/{id}/confirm - save confirmation
- [x] POST /orders/{id}/cancel - cancel order
- [x] POST /orders/{id}/complete - mark complete

**⏱ Estimate:** 3h | **🏷 Labels:** `backend`, `api`, `P0`

---

## GT-035: Payme Service ✅

**✅ Acceptance Criteria:**
- [x] PaymentService class (handles Payme)
- [x] Create invoice (checkout URL)
- [x] Signature generation
- [x] Amount in tiyin (x100)

**⏱ Estimate:** 2h | **🏷 Labels:** `backend`, `payments`, `P0`

---

## GT-036: Payme Webhook ✅

**✅ Acceptance Criteria:**
- [x] POST /api/webhooks/payme
- [x] Signature verification
- [x] CheckPerformTransaction
- [x] CreateTransaction
- [x] PerformTransaction
- [x] CancelTransaction
- [x] Update payment & order status
- [x] Trigger PAID notification

**⏱ Estimate:** 4h | **🏷 Labels:** `backend`, `payments`, `P0`

---

## GT-037: Click Service ✅

**✅ Acceptance Criteria:**
- [x] PaymentService (handles Click)
- [x] Create payment URL
- [x] Signature generation

**⏱ Estimate:** 1.5h | **🏷 Labels:** `backend`, `payments`, `P0`

---

## GT-038: Click Webhook ✅

**✅ Acceptance Criteria:**
- [x] POST /api/webhooks/click
- [x] Signature verification
- [x] Prepare action (action=0)
- [x] Complete action (action=1)
- [x] Update payment & order status
- [x] Trigger PAID notification

**⏱ Estimate:** 3h | **🏷 Labels:** `backend`, `payments`, `P0`

---

## GT-039: PAID Notification ✅

**✅ Acceptance Criteria:**
- [x] Trigger on payment webhook (status=PAID)
- [x] Send to OPS group
- [x] Include: order number, amount, provider
- [x] Queue job

**⏱ Estimate:** 1h | **🏷 Labels:** `backend`, `telegram`, `P1`

---

## GT-040: READY Notification ✅ (Resend tugmasi qolgan)

**✅ Acceptance Criteria:**
- [x] Trigger when: Confirmed + Paid + Reserved
- [x] Send to Therapist group
- [x] Full work order info
- [x] Links: master_day_link, order_link
- [x] Duplicate prevention
- [ ] "Resend READY" tugmasi admin sahifada (qolgan)

**⏱ Estimate:** 2h | **🏷 Labels:** `backend`, `telegram`, `P1`

---

## GT-041: Masters CRUD ✅

**✅ Acceptance Criteria:**
- [x] Masters list table
- [x] Add master form (name, phone, bio, photo upload)
- [x] Edit master
- [x] Toggle active/inactive
- [x] Regenerate token button

**⏱ Estimate:** 3h | **🏷 Labels:** `frontend`, `admin`, `P1`

---

## GT-042: Slots Management ✅

**✅ Acceptance Criteria:**
- [x] Calendar view (by master, by date)
- [x] Bulk slot generation (date range, time range)
- [x] Block/unblock individual slots
- [x] View slot status

**⏱ Estimate:** 3h | **🏷 Labels:** `frontend`, `admin`, `P1`

---

## 📊 Summary

| ID | Task | Hours | Status |
|----|------|-------|--------|
| GT-029 | Admin Login | 1.5h | ✅ |
| GT-030 | Admin Layout | 2h | ✅ |
| GT-031 | Orders List | 4h | ✅ |
| GT-032 | Order Card (A-D) | 4h | ✅ |
| GT-033 | Order Card (E-G) | 3h | ✅ |
| GT-034 | Order Admin API | 3h | ✅ |
| GT-035 | Payme Service | 2h | ✅ |
| GT-036 | Payme Webhook | 4h | ✅ |
| GT-037 | Click Service | 1.5h | ✅ |
| GT-038 | Click Webhook | 3h | ✅ |
| GT-039 | PAID Notification | 1h | ✅ |
| GT-040 | READY Notification | 2h | ✅ (Resend qolgan) |
| GT-041 | Masters CRUD | 3h | ✅ |
| GT-042 | Slots Management | 3h | ✅ |

**Total: ~22h — ALL DONE ✅** (GT-040 Resend tugmasi qolgan)
