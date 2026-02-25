# 📋 Sprint 2: Public Booking - Trello Tasks

## Sprint Info
- **Sprint:** 2
- **Focus:** Public Booking Flow + Telegram
- **Dates:** 10-16 Fevral 2025
- **Delivery:** 16-Fevral (Yakshanba)
- **Hours:** ~22h work + ~3h buffer
- **Status:** ✅ BAJARILGAN

---

## GT-015: Base UI Components ✅

**📝 Description:**
Reusable Vue componentlarni yaratish.

**✅ Acceptance Criteria:**
- [x] BaseButton (primary, secondary, outline, disabled states)
- [x] BaseInput (label, error, validation states)
- [x] BaseCard (shadow, border variants)
- [x] BaseChip (colors for slot statuses)
- [x] LoadingSpinner
- [x] ErrorMessage

**⏱ Estimate:** 3h | **🏷 Labels:** `frontend`, `components`, `P0`

---

## GT-016: Landing Page ✅

**📝 Description:**
Mobile-first landing page yaratish.

**✅ Acceptance Criteria:**
- [x] Hero section (title, subtitle, CTA button)
- [x] Services section (massage types, prices)
- [x] Masters preview (3-4 cards)
- [x] How it works (3 steps)
- [x] Footer (contacts, links)
- [x] Mobile responsive

**⏱ Estimate:** 3h | **🏷 Labels:** `frontend`, `UI`, `P0`

---

## GT-017: Masters List Page ✅

**📝 Description:**
Ustalar ro'yxati sahifasi.

**✅ Acceptance Criteria:**
- [x] GET /api/v1/masters dan data olish
- [x] Master cards grid (2 columns mobile)
- [x] Card: photo, name, bio (truncated)
- [x] Click → Master detail page
- [x] Loading skeleton
- [x] Empty state

**⏱ Estimate:** 2h | **🏷 Labels:** `frontend`, `UI`, `P0`

---

## GT-018: Master Detail Page ✅

**📝 Description:**
Usta haqida to'liq ma'lumot va slotlar.

**✅ Acceptance Criteria:**
- [x] Master info header (photo, name, full bio)
- [x] Date selector component
- [x] Integration with slots calendar
- [x] Back button (TMA)

**⏱ Estimate:** 2h | **🏷 Labels:** `frontend`, `UI`, `P0`

---

## GT-019: Date Selector Component ✅

**📝 Description:**
7 kunlik date picker.

**✅ Acceptance Criteria:**
- [x] Horizontal scroll chips
- [x] 7 kun (bugundan boshlab)
- [x] Selected state
- [x] Day name + date format
- [x] Today highlight

**⏱ Estimate:** 1.5h | **🏷 Labels:** `frontend`, `components`, `P0`

---

## GT-020: Slots Calendar Component ✅

**📝 Description:**
Vaqt slotlari ko'rsatish.

**✅ Acceptance Criteria:**
- [x] GET /api/v1/masters/{id}/slots?date=YYYY-MM-DD
- [x] Grid layout (time slots)
- [x] Status colors (FREE=green, PENDING=yellow, RESERVED=gray, BLOCKED=red)
- [x] 6-hour rule (disable if < 6h from now)
- [x] Click to select (only FREE)
- [x] Selected state highlight
- [x] Loading state
- [x] Empty state ("Bu kunda slot yo'q")

**⏱ Estimate:** 4h | **🏷 Labels:** `frontend`, `components`, `P0`

---

## GT-021: Slots API Endpoint ✅

**📝 Description:**
Master slotlarini qaytaruvchi API.

**✅ Acceptance Criteria:**
- [x] GET /api/v1/masters/{master}/slots
- [x] Query params: date (required)
- [x] Response: slots with status
- [x] 6-hour rule applied (mark as unavailable)
- [x] Active masters only

**⏱ Estimate:** 2h | **🏷 Labels:** `backend`, `api`, `P0`

---

## GT-022: Massage Type Selection ✅

**📝 Description:**
Massaj turi va yog' tanlash.

**✅ Acceptance Criteria:**
- [x] Massage type cards (Traditional, Relax Oil)
- [x] Price display
- [x] Oil preference (if Relax selected)
- [x] Oil options: Coconut, Lavender, No preference
- [x] Selected state

**⏱ Estimate:** 2h | **🏷 Labels:** `frontend`, `UI`, `P0`

---

## GT-023: Fixed Bottom Bar ✅

**📝 Description:**
Booking uchun fixed bottom bar.

**✅ Acceptance Criteria:**
- [x] Fixed position bottom
- [x] Selected slot info
- [x] Total price
- [x] "Davom etish" button
- [x] Disabled until slot selected
- [x] Safe area padding (mobile)

**⏱ Estimate:** 1.5h | **🏷 Labels:** `frontend`, `UI`, `P0`

---

## GT-024: Booking Form ✅

**📝 Description:**
Buyurtma yaratish formasi.

**✅ Acceptance Criteria:**
- [x] Phone input (required, UZ format +998)
- [x] Name input (optional)
- [x] Comment textarea (optional)
- [x] PD consent checkbox (required)
- [x] Form validation
- [x] Honeypot field (anti-spam)
- [x] Submit button with loading

**⏱ Estimate:** 3h | **🏷 Labels:** `frontend`, `forms`, `P0`

---

## GT-025: Order Creation API ✅

**📝 Description:**
Buyurtma yaratish API endpoint.

**✅ Acceptance Criteria:**
- [x] POST /api/v1/orders
- [x] Validate slot availability
- [x] Double booking prevention (DB transaction + lock)
- [x] 6-hour rule server-side validation
- [x] Create/find customer
- [x] Create order
- [x] Update slot: FREE → PENDING
- [x] Return order with public_token
- [x] Trigger Telegram notification (async)

**⏱ Estimate:** 4h | **🏷 Labels:** `backend`, `api`, `P0`

---

## GT-026: Order Success/Error Pages ✅

**📝 Description:**
Buyurtma natijasi sahifalari.

**✅ Acceptance Criteria:**
- [x] Success page (order number, next steps)
- [x] Error page (try again button)
- [x] Slot taken error (specific message)
- [x] TMA close button

**⏱ Estimate:** 1.5h | **🏷 Labels:** `frontend`, `UI`, `P0`

---

## GT-027: Telegram Service ✅

**📝 Description:**
Telegram bot xabar yuborish service.

**✅ Acceptance Criteria:**
- [x] TelegramService class
- [x] Send message method
- [x] Message formatting (Markdown)
- [x] Error handling
- [x] Retry logic
- [x] Log to telegram_messages table

**⏱ Estimate:** 2h | **🏷 Labels:** `backend`, `telegram`, `P0`

---

## GT-028: NEW Order Notification ✅

**📝 Description:**
Yangi buyurtma haqida Telegram xabar.

**✅ Acceptance Criteria:**
- [x] Trigger on order creation
- [x] Send to OPS group
- [x] Message includes: order number, customer phone, master, date/time, massage type
- [x] Admin link to order
- [x] Queue job (async)

**⏱ Estimate:** 2h | **🏷 Labels:** `backend`, `telegram`, `P1`

---

## 📊 Summary

| ID | Task | Hours | Status |
|----|------|-------|--------|
| GT-015 | Base UI Components | 3h | ✅ |
| GT-016 | Landing Page | 3h | ✅ |
| GT-017 | Masters List | 2h | ✅ |
| GT-018 | Master Detail | 2h | ✅ |
| GT-019 | Date Selector | 1.5h | ✅ |
| GT-020 | Slots Calendar | 4h | ✅ |
| GT-021 | Slots API | 2h | ✅ |
| GT-022 | Massage Selection | 2h | ✅ |
| GT-023 | Bottom Bar | 1.5h | ✅ |
| GT-024 | Booking Form | 3h | ✅ |
| GT-025 | Order Creation API | 4h | ✅ |
| GT-026 | Success/Error Pages | 1.5h | ✅ |
| GT-027 | Telegram Service | 2h | ✅ |
| GT-028 | NEW Notification | 2h | ✅ |

**Total: ~22h — ALL DONE ✅**
