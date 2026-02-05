# Golden Touch - AI-Assisted Development Setup

## Project Overview

**Golden Touch** is a massage booking MVP (Laravel 12.x + Inertia.js + Vue 3) for validating the business hypothesis.

### Key Features
- Public booking site (iPhone-style UI)
- Customer portal with OTP login
- Dispatcher admin panel
- Therapist day view (public, no auth)
- Payme/Click payment integration
- Telegram notifications (2 bots, 2 groups)

---

## AI Guidelines Structure

```
.ai/
├── guidelines/project/
│   ├── architecture.blade.php      # Project overview, models, directory structure
│   ├── golden-touch.blade.php      # Business logic and user flows
│   ├── slot-management.blade.php   # Slot status management
│   ├── order-workflow.blade.php    # Order processing workflow
│   ├── permissions.blade.php       # Roles and permissions
│   └── translatable.blade.php      # Multi-language setup
│
└── skills/
    ├── order-processing/           # Order lifecycle management
    │   └── SKILL.md
    ├── payment-integration/        # Payme/Click integration
    │   └── SKILL.md
    ├── telegram-notifications/     # Telegram bot setup
    │   └── SKILL.md
    ├── translatable-models/        # Multi-language models
    │   └── SKILL.md
    ├── permission-system/          # Role/permission system
    │   └── SKILL.md
    └── spatie-integration/         # Spatie packages guide
        └── SKILL.md
```

---

## Guidelines Overview

### 1. Architecture (`architecture.blade.php`)
**Provides context about:**
- Tech stack (Laravel, Vue 3, Inertia.js)
- Database models (Client, Therapist, Slot, Order, etc.)
- Directory structure
- Key business rules
- Environment variables
- Test data

**When AI uses this:** Understanding project structure, creating new features

### 2. Golden Touch (`golden-touch.blade.php`)
**Covers:**
- Services offered (massage types, pricing)
- Client booking flow (7 steps)
- Dispatcher workflow (8 blocks)
- Customer portal flow
- Therapist day view
- UI requirements (iPhone-style)
- Error messages

**When AI uses this:** Implementing user-facing features, understanding business logic

### 3. Slot Management (`slot-management.blade.php`)
**Explains:**
- Slot statuses (FREE, PENDING, RESERVED, BLOCKED)
- Status transitions
- Double-booking prevention
- Slot generation
- Frontend display

**When AI uses this:** Working with slot booking logic

### 4. Order Workflow (`order-workflow.blade.php`)
**Details:**
- Order statuses and transitions
- Payment statuses
- Complete flow diagram
- Service implementation
- Admin controller patterns
- CSV export

**When AI uses this:** Implementing order management features

### 5. Permissions (`permissions.blade.php`)
**Covers:**
- Roles (admin, owner, dispatcher)
- Permission matrix
- Authorization patterns
- Vue/Blade integration
- Testing permissions

**When AI uses this:** Implementing access control

### 6. Translatable (`translatable.blade.php`)
**Explains:**
- Supported languages (en, uz, ru)
- Translatable model setup
- CRUD operations
- Blade/Controller integration

**When AI uses this:** Multi-language features

---

## Skills Overview

### 1. Order Processing (`order-processing/SKILL.md`)
**Purpose:** Complete guide for implementing order lifecycle

**Covers:**
- Order model with constants
- Related models (Confirmation, Quality, AuditLog)
- OrderService implementation
- API endpoints
- Vue components
- Testing

**Use when:** Creating order-related features

### 2. Payment Integration (`payment-integration/SKILL.md`)
**Purpose:** Payme and Click payment integration

**Covers:**
- Configuration
- PaymentService implementation
- Webhook handlers
- Signature verification
- Admin UI
- Testing webhooks

**Use when:** Working with payments

### 3. Telegram Notifications (`telegram-notifications/SKILL.md`)
**Purpose:** Telegram bot notification system

**Covers:**
- 2 bots / 2 groups architecture
- TelegramService implementation
- Message templates (NEW, PAID, READY)
- Integration points
- Error handling
- Resend feature

**Use when:** Implementing notifications

---

## Example AI Workflow

**Task:** "Add customer can view their past orders in cabinet"

1. **AI reads:** `architecture.blade.php` → Understands models (Client, Order)
2. **AI reads:** `golden-touch.blade.php` → Understands cabinet flow
3. **AI reads:** `order-processing/SKILL.md` → Gets Order model details
4. **AI generates:**
   - CabinetController with order listing
   - Vue page for order history
   - Following existing patterns

---

## Development Commands

```bash
# Run local development
php artisan serve
npm run dev

# Database
php artisan migrate:fresh --seed

# Clear caches
php artisan cache:clear
php artisan permission:cache-reset
php artisan config:clear

# Testing
php artisan test
```

---

## Key Patterns

### 1. Service Classes
All business logic in service classes:
```php
app(OrderService::class)->create($data);
app(PaymentService::class)->createInvoice($order, 'payme');
app(TelegramService::class)->sendNew($order);
```

### 2. Status Constants
Use model constants for statuses:
```php
Order::STATUS_NEW
Order::PAY_PAID
Slot::STATUS_FREE
```

### 3. Audit Logging
Log all status changes:
```php
$order->logEvent('status_changed', ['from' => $old, 'to' => $new]);
```

### 4. Transaction Safety
Use DB transactions for critical operations:
```php
DB::transaction(function () use ($data) {
    $slot = Slot::lockForUpdate()->find($slotId);
    // ... create order, update slot
});
```

---

## Testing

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter=OrderTest

# With coverage
php artisan test --coverage
```

---

## Resources

- [Laravel Boost Docs](https://boost.laravel.com)
- [Spatie Permission](https://spatie.be/docs/laravel-permission)
- [Spatie Translatable](https://spatie.be/docs/laravel-translatable)
- [Inertia.js](https://inertiajs.com)
- [Payme Developer](https://developer.payme.uz)
- [Click Developer](https://docs.click.uz)
