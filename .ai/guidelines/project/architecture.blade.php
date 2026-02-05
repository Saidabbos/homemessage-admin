## Golden Touch - Project Architecture & Structure

This is a **massage booking MVP** (Laravel 12.x + Inertia.js + Vue 3) for validating the business hypothesis.

### Project Goals (MVP0)
- Accept massage bookings (60 min sessions, 30 min gaps)
- Prevent double bookings with slot status management
- Manual dispatching workflow (no auto-assignment)
- Payment integration (Payme/Click API + Webhooks)
- Telegram notifications for operations
- Quality control data collection

### Tech Stack
- **Backend**: PHP 8.x / Laravel 12.x
- **Frontend**: Vue 3 + Inertia.js (iPhone-style UI, mobile-first)
- **Database**: MySQL / MariaDB
- **Payments**: Payme/Click API + Webhooks
- **Notifications**: Telegram Bot API (2 bots, 2 groups)
- **Hosting**: Shared hosting with SSL (Let's Encrypt)

### Core Packages
- **spatie/laravel-permission** - Role and permission management
- **spatie/laravel-translatable** - Multi-language support (en, uz, ru)
- **laravel/boost** - AI-assisted development
- **inertiajs/inertia-laravel** - SPA-like experience

---

### Database Models

#### User (with Roles & Permissions)
```php
// Roles: admin, dispatcher, owner
// Uses HasRoles trait from Spatie\Permission

$user->assignRole('dispatcher');
$user->hasPermissionTo('manage orders');
```

#### Client (Customer)
```php
// clients table: id, phone (unique), name, comment, phone_verified_at, timestamps
// Has many addresses and orders

class Client extends Model
{
    protected $fillable = ['phone', 'name', 'comment'];

    public function addresses() { return $this->hasMany(ClientAddress::class); }
    public function orders() { return $this->hasMany(Order::class); }
}
```

#### ClientAddress
```php
// client_addresses table: id, client_id, address, entrance, floor, elevator,
//   parking, landmark, onsite_phone, constraints, space_ok, pets, is_default
```

#### Therapist (Master/Masseur)
```php
// therapists table: id, name, phone, telegram_chat_id, rating, is_active,
//   public_token, photo, bio, timestamps

class Therapist extends Model
{
    use HasTranslations;
    public $translatable = ['bio'];

    public function slots() { return $this->hasMany(Slot::class); }
    public function orders() { return $this->hasMany(Order::class); }
}
```

#### MassageType
```php
// massage_types table: id, slug, name, duration, price, oil_required, is_active
// Types: Traditional Thai (60 min), Relax Oil Massage (60 min)
// Price: 500,000 UZS
```

#### OilType
```php
// oil_types table: id, slug, name, is_active
// Types: Coconut oil, Lavender oil, No preference
```

#### Slot
```php
// slots table: id, therapist_id, date, start_time, end_time, status, timestamps

// STATUSES:
const STATUS_FREE = 'FREE';           // Available for booking
const STATUS_PENDING = 'PENDING';     // Order created, awaiting confirmation
const STATUS_RESERVED = 'RESERVED';   // Paid and confirmed
const STATUS_BLOCKED = 'BLOCKED';     // Manually blocked (day off, etc.)
```

#### Order
```php
// orders table: id, client_id, therapist_id, slot_id, massage_type_id, oil_type_id,
//   public_token, status, payment_status, paid_at, total_amount,
//   pay_provider, pay_reference, pay_url, pay_expires_at,
//   work_order_status, work_order_text, sent_to_therapist_at,
//   ready_sent_at, timestamps

// ORDER STATUSES:
const STATUS_NEW = 'NEW';
const STATUS_CONFIRMING = 'CONFIRMING';
const STATUS_WAITING_PAYMENT = 'WAITING_PAYMENT';
const STATUS_PAID = 'PAID';
const STATUS_RESERVED = 'RESERVED';
const STATUS_COMPLETED = 'COMPLETED';
const STATUS_CANCELLED = 'CANCELLED';
const STATUS_CANCEL_REQUESTED = 'CANCEL_REQUESTED';

// PAYMENT STATUSES:
const PAY_NOT_INVOICED = 'NOT_INVOICED';
const PAY_INVOICED = 'INVOICED';
const PAY_PAID = 'PAID';
const PAY_FAILED = 'FAILED';
const PAY_REFUNDED = 'REFUNDED';
```

#### OrderConfirmation (Pre-payment questionnaire)
```php
// order_confirmations table: order_id, address, entrance, floor, elevator,
//   parking, landmark, onsite_phone, constraints, space_ok, pets,
//   note_to_therapist, call_outcome, filled_by, filled_at

// CALL OUTCOMES:
const OUTCOME_CONFIRMED = 'CONFIRMED';
const OUTCOME_RESCHEDULE = 'RESCHEDULE';
const OUTCOME_NO_ANSWER = 'NO_ANSWER';
const OUTCOME_CANCELLED = 'CANCELLED';
```

#### OrderQuality (Post-service QA)
```php
// order_qualities table: order_id, rating_punctuality, rating_quality,
//   rating_communication, rating_overall, will_order_again, recommend,
//   hygiene_issue, hygiene_comment, improvement_comment, filled_by, filled_at
```

#### OrderAuditLog
```php
// order_audit_logs table: id, order_id, event, user_id, data (json), created_at
// Events: created, status_changed, paid, reserved, cancelled, completed, ready_sent
```

---

### Directory Structure

```
.ai/
├── guidelines/project/
│   ├── architecture.blade.php      # This file - project overview
│   ├── golden-touch.blade.php      # Business logic and flows
│   ├── slot-management.blade.php   # Slot status management
│   ├── order-workflow.blade.php    # Order processing workflow
│   ├── permissions.blade.php       # Roles and permissions
│   └── translatable.blade.php      # Multi-language setup
└── skills/
    ├── order-processing/           # Order lifecycle management
    ├── payment-integration/        # Payme/Click integration
    ├── telegram-notifications/     # Telegram bot setup
    └── ...

app/
├── Models/
│   ├── User.php                    # Admin/Dispatcher users
│   ├── Client.php                  # Customer accounts
│   ├── ClientAddress.php           # Saved addresses
│   ├── Therapist.php               # Massage masters
│   ├── MassageType.php             # Service types
│   ├── OilType.php                 # Oil options
│   ├── Slot.php                    # Time slots
│   ├── Order.php                   # Bookings
│   ├── OrderConfirmation.php       # Pre-payment questionnaire
│   ├── OrderQuality.php            # Post-service QA
│   └── OrderAuditLog.php           # Event logging
├── Http/
│   ├── Controllers/
│   │   ├── Public/                 # Client-facing booking
│   │   ├── Admin/                  # Dispatcher admin panel
│   │   ├── Cabinet/                # Customer portal (OTP)
│   │   └── Api/                    # Payment webhooks
│   └── Middleware/
├── Services/
│   ├── SlotService.php             # Slot management
│   ├── OrderService.php            # Order processing
│   ├── PaymentService.php          # Payme/Click integration
│   ├── TelegramService.php         # Notifications
│   ├── OtpService.php              # SMS OTP handling
│   └── WorkOrderService.php        # Digital work order generation
└── Policies/

resources/
├── js/
│   ├── Pages/
│   │   ├── Public/                 # Booking flow (iPhone UI)
│   │   ├── Admin/                  # Dispatcher dashboard
│   │   └── Cabinet/                # Customer portal
│   └── Components/
└── views/
```

---

### Key Business Rules

#### 1. Slot Management
```php
// Slots are 60 min with 30 min gaps between sessions
// Minimum 2 hours lead time for booking

$slot->canBook(); // Check if FREE and >= 2 hours ahead
$slot->markPending($orderId);
$slot->markReserved($orderId);
$slot->release(); // Return to FREE on cancellation
```

#### 2. Order Flow
```
Client selects → FREE slot becomes PENDING → Dispatcher calls →
Fills confirmation form → Creates invoice (Payme/Click) →
Webhook confirms PAID → Slot becomes RESERVED →
Digital work order sent → Service delivered → QA filled → COMPLETED
```

#### 3. Payment Flow (API + Webhooks)
```php
// 1. Dispatcher creates invoice
$payment = PaymentService::createInvoice($order, 'payme');
// Returns: pay_url, pay_reference

// 2. Client pays via link

// 3. Webhook receives confirmation
// POST /api/payments/webhook/payme
PaymentService::handleWebhook($provider, $payload);

// 4. Auto-updates: payment_status=PAID, slot=RESERVED
```

#### 4. Telegram Notifications
```php
// 2 Bots, 2 Groups:
// Bot A (OPS) → Group #1 (Owner+Dispatcher): NEW, PAID events
// Bot B (THERAPIST) → Group #2 (Masters+Owner+Dispatcher): READY event

TelegramService::sendNew($order);      // On order creation
TelegramService::sendPaid($order);     // On payment confirmed
TelegramService::sendReady($order);    // When all conditions met
```

---

### Environment Variables

```env
# Telegram
OPS_BOT_TOKEN=xxx
OPS_GROUP_CHAT_ID=xxx
THERAPIST_BOT_TOKEN=xxx
THERAPIST_GROUP_CHAT_ID=xxx

# Payments
PAYME_MERCHANT_ID=xxx
PAYME_SECRET_KEY=xxx
CLICK_MERCHANT_ID=xxx
CLICK_SECRET_KEY=xxx

# SMS OTP
SMS_PROVIDER=eskiz
SMS_API_KEY=xxx

# App
APP_URL=https://goldentouch.uz
BOOKING_LEAD_TIME_HOURS=2
SESSION_DURATION_MINUTES=60
SESSION_GAP_MINUTES=30
DEFAULT_PRICE=500000
```

---

### Test Data

```
# Admin Panel (/admin)
admin@example.com → admin role → all permissions
dispatcher@example.com → dispatcher role → order management

# Customer Portal (/cabinet)
+998901234567 → OTP login → view orders, pay

# Public Booking
/booking → select therapist → select slot → enter phone → submit

Password for seeded users: password
```

---

### Important Conventions

1. **Prices**: Store in UZS as integers (500000 = 500,000 UZS)
2. **Tokens**: Use 32+ character random strings for public_token
3. **Status Transitions**: Always use service classes, never update directly
4. **Audit Logging**: Log all status changes with user_id and timestamp
5. **Double-booking Prevention**: Check slot status in DB transaction
6. **Webhook Security**: Verify signatures, use IP allowlist
7. **Cache**: Clear permission cache after changes
