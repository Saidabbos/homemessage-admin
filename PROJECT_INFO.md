# HomeMessage - Service Booking Platform

## 📱 Loyiha Tavsifi

**HomeMessage** - Uyga massage/beauty services booking qilish platforma.

### Asosiy Xususiyatlar:
- Customers - Serviceler book qiladi
- Service Providers - Xizmatlarni taqdim etadi
- Admins - System manage qiladi
- Multi-language support (en, uz, ru)
- Role-based access control
- Service booking management

---

## 👥 User Roles

### 1. Admin
- System manage qiladi
- Users manage qiladi
- Services manage qiladi
- Bookings monitor qiladi
- Reports ko'radi
- **Login**: Alohida `/admin/login` sahifasidan

### 2. Service Provider
- Profile manage qiladi
- Services add/edit qiladi
- Bookings accept/reject qiladi
- Ratings ko'radi

### 3. Customer
- Services browse qiladi
- Bookings create qiladi
- Ratings beradi
- History ko'radi

---

## 🔐 Current Admin Account

```
Email: admin@example.com
Password: password
Role: admin
Status: Active
```

---

## 🛣️ Admin Routes

```
GET  /admin/login             - Admin login page
POST /admin/login             - Admin login submit
GET  /admin/logout            - Admin logout

GET  /admin/dashboard         - Admin dashboard (protected)
GET  /admin/users             - Users management
GET  /admin/services          - Services management
GET  /admin/bookings          - Bookings management
GET  /admin/reports           - Reports & Analytics
```

---

## 🗄️ Database Models

### User (Already exists)
- id, name, email, password
- user_type (customer, provider, admin)
- role (via Spatie Permission)
- status, created_at, updated_at

### Service (To be created)
- id, name, slug, description, price
- category_id
- translations: name, description
- created_by (service_provider_id)
- status, created_at, updated_at

### Booking (To be created)
- id, customer_id, service_id, date, time
- duration, total_price, status
- notes, created_at, updated_at

### ServiceCategory (To be created)
- id, name, slug
- translations: name, description
- status, created_at, updated_at

---

## 🎯 Implementation Stages

1. ✅ **Base Setup** - Docker, Laravel, Spatie packages, Boost
2. ⏳ **Admin Auth** - Admin login/logout, separate session
3. ⏳ **Admin Panel** - Dashboard, user management, analytics
4. ⏳ **Services** - Service CRUD, categories
5. ⏳ **Bookings** - Booking management
6. ⏳ **Customers** - Customer features
7. ⏳ **Payments** - Payment integration
8. ⏳ **Ratings** - Review system

---

## 🎨 Project Structure

```
app/
├── Models/
│   ├── User.php
│   ├── Service.php
│   ├── ServiceCategory.php
│   ├── Booking.php
│   └── Rating.php
│
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   │   ├── AuthController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── UserController.php
│   │   │   ├── ServiceController.php
│   │   │   └── BookingController.php
│   │   └── Customer/
│   │       └── BookingController.php
│   │
│   └── Middleware/
│       └── AdminMiddleware.php
│
└── Policies/
    ├── UserPolicy.php
    └── ServicePolicy.php

resources/
└── views/
    ├── admin/
    │   ├── auth/
    │   │   ├── login.blade.php
    │   │   └── layout.blade.php
    │   ├── dashboard/
    │   │   ├── index.blade.php
    │   │   ├── users/
    │   │   ├── services/
    │   │   └── bookings/
    │   └── layouts/
    │       └── app.blade.php
    │
    └── customer/
        └── ...

database/
└── migrations/
    ├── create_services_table.php
    ├── create_service_categories_table.php
    ├── create_bookings_table.php
    └── create_ratings_table.php
```

---

## 🔄 Features Roadmap

### Phase 1: Admin Panel ⏳
- [x] Admin account
- [ ] Admin login page
- [ ] Admin dashboard
- [ ] User management
- [ ] System settings

### Phase 2: Services
- [ ] Service model & CRUD
- [ ] Service categories
- [ ] Service search & filter
- [ ] Multi-language support

### Phase 3: Bookings
- [ ] Booking model & CRUD
- [ ] Booking status management
- [ ] Calendar integration
- [ ] Notifications

### Phase 4: Customer Features
- [ ] Customer dashboard
- [ ] Service browsing
- [ ] Booking management
- [ ] Profile management

### Phase 5: Advanced
- [ ] Payments
- [ ] Ratings & Reviews
- [ ] Analytics
- [ ] Reports
