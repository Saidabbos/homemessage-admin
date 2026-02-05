## Golden Touch - Business Logic & User Flows

This document describes the core business flows and requirements for the Golden Touch massage booking MVP.

---

### Product Overview

**Golden Touch** is a massage booking platform with:
- Public booking site (iPhone-style UI)
- Customer portal (OTP login)
- Dispatcher admin panel
- Therapist day view (public, no auth)
- Telegram notifications

**MVP0 Goal**: Validate hypothesis with first paid orders, prevent double bookings, collect quality data.

---

### Services Offered (MVP0)

| Service | Duration | Gap | Price | Oil Required |
|---------|----------|-----|-------|--------------|
| Traditional Thai | 60 min | 30 min | 500,000 UZS | No |
| Relax Oil Massage | 60 min | 30 min | 500,000 UZS | Yes |

**Oil Options** (only for Relax Oil Massage):
- Coconut oil
- Lavender oil
- No preference

---

### User Journeys

#### 1. Client Booking Flow (Public Website)

```
Step 1: Select Massage Type
├── Traditional Thai (60 min)
└── Relax Oil Massage (60 min)
    └── Step 1.1: Select Oil (Coconut/Lavender/No preference)

Step 2: Select Therapist
├── Show therapist cards (photo, name, rating)
└── Tap to select

Step 3: Select Date
├── Horizontal chips for 7 days (today + 6)
└── Tap to select day

Step 4: Select Time Slot
├── Show slots for selected day
├── FREE slots: clickable
├── PENDING/RESERVED/BLOCKED: disabled with label
└── Tap to select FREE slot

Step 5: Bottom Bar
├── Total: 500,000 UZS (fixed)
└── Next button (active when all selections made)

Step 6: Contact Details
├── Phone* (required, +998 format)
├── Name (optional)
├── Comment (optional, 300 chars)
├── Privacy consent checkbox* (required)
└── Send Request button

Step 7: Confirmation Screen
└── "Request received. Dispatcher will confirm shortly."
```

**Business Rules**:
- Minimum 2 hours lead time for booking
- Maximum 6 slots per order (if multiple allowed)
- Slots with < 2 hours disabled and server-side rejected
- FREE → PENDING on order creation
- Honeypot anti-spam required

---

#### 2. Dispatcher Flow (Admin Panel)

```
A. Receive Notification
├── Telegram: NEW order in OPS group
└── Admin panel: Order appears in list

B. Open Order Card
├── Block A: Client info (phone, name, comment)
├── Block B: Order details (massage, oil, therapist, slot, status)
├── Block C: Confirmation form (address, entrance, floor, etc.)
├── Block D: Payment (create invoice, status)
├── Block E: Booking (confirm/cancel)
├── Block F: Work Order (generate, copy, send)
└── Block G: Quality Control (post-service)

C. Call Client & Fill Confirmation Form
├── Address, entrance, floor, elevator
├── Parking, landmark, on-site phone
├── Constraints, space 2x2, pets
├── Note to therapist
└── Call outcome: Confirmed/Reschedule/No answer/Cancelled

D. Handle Call Outcome
├── Confirmed → Continue to payment
├── Reschedule → Change slot → Continue
├── No answer → Slot returns to FREE
└── Cancelled → Slot returns to FREE

E. Create Invoice & Wait for Payment
├── Select method: Payme or Click
├── Amount: 500,000 UZS (editable)
├── Create invoice → Get payment URL
├── Send URL to client (SMS)
├── Wait for webhook (auto PAID)
└── Telegram: PAID notification

F. Confirm Booking
├── After PAID: "Confirm Booking" enabled
├── Slot becomes RESERVED
└── Telegram: READY to therapist group

G. Generate & Send Work Order
├── "Generate Work Order" creates text
├── "Copy" to clipboard for manual send
├── "Send via Telegram" (if therapist has chat_id)
└── Mark as sent

H. Post-Service Quality Control
├── Call client after service
├── Fill QA form (ratings 1-10, yes/no, comments)
├── "Complete Order" button
└── Order status → COMPLETED
```

---

#### 3. Customer Portal (Cabinet)

```
Entry: /cabinet

A. Login Flow
├── Enter phone (+998...)
├── Receive OTP via SMS
├── Enter OTP (4-6 digits)
├── Success → Dashboard
└── New phone → Auto-create account

B. Dashboard
├── Profile section (name, phone, addresses)
├── Order history (all orders, filter by status)
└── Active orders (with payment action)

C. Order Card
├── Service details (massage, therapist, time)
├── Address info
├── Status badge
├── Payment status
└── Actions: Pay (if WAITING_PAYMENT), Request Cancel (if RESERVED)

D. Payment Action
├── If INVOICED/WAITING_PAYMENT
├── Show "Pay Now" button
└── Redirects to Payme/Click checkout

E. Cancel Flow
├── Before RESERVED: Direct cancel → FREE slot
└── After RESERVED: Request cancel → CANCEL_REQUESTED → Dispatcher decides
```

**OTP Security**:
- TTL: 2-5 minutes
- Max attempts: 5, then 15-min block
- Resend: max 1 per 60 sec, 5 per hour
- Rate limit by IP

---

#### 4. Therapist Day View (Public, No Auth)

```
URL: /m/{master_token}/day/{YYYY-MM-DD}

Features:
├── List of orders for the day (sorted by time)
├── Each order shows: time, massage type, status
├── "Details" link to order view
└── Only shows orders for THIS therapist

Order Detail URL: /o/{order_public_token}

Order Detail Shows:
├── Time, massage type, oil
├── Address, entrance, floor, elevator
├── Parking, landmark
├── Constraints, note to therapist
├── Client phone, on-site phone
└── NO admin buttons (read-only)

Security:
├── Tokens are 32+ chars random strings
├── Token in URL, not ID
├── Regenerate token if leaked
└── Each therapist/order has unique token
```

---

### UI Requirements (iPhone-Style)

```
Visual Style:
├── Mobile-first, premium minimal
├── Large titles, clean spacing
├── Glass effect (blur, transparency)
├── Soft shadows, rounded corners
├── SF Pro or similar system font
├── "Traffic light" status colors

Fixed Bottom Bar:
├── Always visible on booking screens
├── Shows "Total: 500,000 UZS"
├── "Next" button (right side)
└── Next disabled until all selections made

Slot Display:
├── FREE: Active button, selectable
├── PENDING: Disabled, label "In progress"
├── RESERVED: Disabled, label "Booked"
├── BLOCKED: Disabled, label "Closed"
└── <2 hours: Disabled (lead time rule)

Responsive:
├── Mobile: Native app feel
├── Tablet: Scaled mobile
└── Desktop: Centered container, no grid break
```

---

### Data Export (CSV)

**Export includes** (for date range):
- Order ID, created_at
- Client: phone, name
- Service: massage_type, oil_type
- Therapist: name
- Slot: date, time
- Confirmation form: all fields
- Payment: status, method, amount, paid_at
- Work order: status, sent_at
- QA: all ratings and comments
- Order status, slot status

---

### Audit Trail Requirements

**Logged Events**:
- `created`: Order created
- `status_changed`: Any status change (from/to)
- `paid`: Payment confirmed
- `reserved`: Slot reserved
- `cancelled`: Order cancelled (with reason)
- `completed`: Order completed
- `ready_sent`: READY notification sent to therapists

**Log Fields**:
- order_id
- event type
- user_id (who performed)
- data (JSON with details)
- created_at

---

### Error Messages

| Scenario | Message |
|----------|---------|
| Slot taken during submission | "This time is unavailable. Please choose another slot." |
| Lead time violation | "Bookings require at least 2 hours notice." |
| Invalid phone | "Please enter a valid phone number." |
| OTP expired | "Code expired. Please request a new one." |
| OTP wrong | "Invalid code. X attempts remaining." |
| Payment failed | "Payment could not be processed. Please try again." |
| Server error | "Something went wrong. Please try again later." |

---

### Localization

**Supported Languages**: en, uz, ru

**Translatable Content**:
- Therapist bio
- Massage type names/descriptions
- UI strings (via Laravel lang files)
- Error messages

**Default**: en
**Fallback**: en
