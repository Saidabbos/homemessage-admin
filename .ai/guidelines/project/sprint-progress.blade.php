## Sprint Progress & Roadmap - HomeMessage

This document tracks sprint progress and provides a roadmap for the MVP development.

---

### Sprint Overview

| Sprint | Focus | Dates | Status |
|--------|-------|-------|--------|
| **Sprint 1** | Foundation + Admin CRUD | 3-9 Feb | ✅ In Progress |
| **Sprint 2** | Public Booking + Telegram | 10-16 Feb | ⏳ Pending |
| **Sprint 3** | Admin Orders + Payments | 17-23 Feb | ⏳ Pending |
| **Sprint 4** | Customer Portal + QA | 24 Feb - 2 Mar | ⏳ Pending |

---

### Sprint 1: Foundation + Admin CRUD

**Completed:**
- [x] Laravel 12 + Inertia.js + Vue 3 setup
- [x] Docker configuration (PHP, MySQL, Nginx)
- [x] AdminLTE styled admin panel
- [x] Spatie Permission integration
- [x] vue-i18n (uz, ru, en)
- [x] ServiceTypes CRUD (translatable)
- [x] Oils CRUD (translatable)
- [x] StandardItems CRUD
- [x] Masters CRUD (with service types, oils relations)
- [x] Dispatchers management
- [x] Customers management
- [x] Settings page
- [x] Profile page
- [x] Clean Architecture (Services, Repositories, Form Requests)

**Remaining:**
- [ ] Slots table and management
- [ ] Customers table enhancements (addresses)
- [ ] Orders table
- [ ] Payments table
- [ ] Enums (SlotStatus, OrderStatus, PaymentStatus)

---

### Sprint 2: Public Booking + Telegram

**Tasks:**
- [ ] Base UI Components (BaseButton, BaseInput, BaseCard, etc.)
- [ ] Landing Page (mobile-first)
- [ ] Masters List Page
- [ ] Master Detail Page
- [ ] Date Selector Component (7-day horizontal)
- [ ] Slots Calendar Component
- [ ] Slots API Endpoint
- [ ] Massage Type Selection
- [ ] Fixed Bottom Bar
- [ ] Booking Form
- [ ] Order Creation API (with double-booking prevention)
- [ ] Success/Error Pages
- [ ] Telegram Service
- [ ] NEW Order Notification

---

### Sprint 3: Admin Orders + Payments

**Tasks:**
- [ ] Admin Login Page
- [ ] Admin Layout refinements
- [ ] Orders List Page (filters, pagination)
- [ ] Order Card Page - Part 1 (Blocks A-D)
- [ ] Order Card Page - Part 2 (Blocks E-G)
- [ ] Order Admin API
- [ ] Payme Service
- [ ] Payme Webhook
- [ ] Click Service
- [ ] Click Webhook
- [ ] PAID Notification
- [ ] READY Notification
- [ ] Masters CRUD enhancements
- [ ] Slots Management (calendar, bulk generation)

---

### Sprint 4: Customer Portal + QA

**Tasks:**
- [ ] Eskiz OTP Service
- [ ] Customer OTP Login - Backend
- [ ] Customer OTP Login - Frontend
- [ ] Customer Orders List
- [ ] Customer Order Detail
- [ ] Customer Profile
- [ ] Customer Addresses CRUD
- [ ] Cancel Request
- [ ] Master Day View Page
- [ ] Master Order Detail Page
- [ ] Master Pages API
- [ ] QA Form Component
- [ ] Complete Order Flow
- [ ] CSV Export
- [ ] Audit Logs

---

### Key Entities & Status Flows

**Slot Statuses:**
```
FREE → PENDING → RESERVED
         ↓
      BLOCKED (manual)
```

**Order Statuses:**
```
NEW → CONFIRMING → WAITING_PAYMENT → PAID → RESERVED → COMPLETED
  ↓        ↓              ↓           ↓         ↓
CANCELLED ←──────────────────────────────── CANCEL_REQUESTED
```

**Payment Statuses:**
```
NOT_INVOICED → INVOICED → PAID
                  ↓
               FAILED → REFUNDED
```

---

### Business Rules

1. **Order Number Format:** `GT-YYYYMMDD-XXX`
2. **6-hour rule:** Slotlar 6 soatdan kam vaqt qolsa unavailable
3. **Lead time:** Minimum 2 soat oldindan buyurtma
4. **Session duration:** 60 min massage + 30 min gap
5. **Default price:** 500,000 UZS
6. **Double-booking prevention:** DB transaction + lock

---

### Database Tables (Full List)

**Completed:**
- [x] users (Spatie roles/permissions)
- [x] service_types (translatable)
- [x] oils (translatable)
- [x] standard_items (translatable)
- [x] masters (with user_id)
- [x] master_service_type (pivot)
- [x] master_oil (pivot)
- [x] settings

**Pending:**
- [ ] slots
- [ ] customers (enhanced)
- [ ] customer_addresses
- [ ] otp_codes
- [ ] orders
- [ ] order_confirmations
- [ ] order_qualities
- [ ] order_audit_logs
- [ ] payments
- [ ] telegram_messages

---

### Sprint Files Location

Detailed task breakdowns are in `sprints/` folder:
- `sprint-1-tasks.md` - Foundation + APIs
- `sprint-2-tasks.md` - Public Booking + Telegram
- `sprint-3-tasks.md` - Admin + Payments
- `sprint-4-tasks.md` - Customer Portal + QA

Each task includes:
- Description
- Acceptance criteria
- Technical implementation details
- Time estimate
- Priority labels
