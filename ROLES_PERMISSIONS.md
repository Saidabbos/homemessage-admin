# Sabai - Roles & Permissions System

## 🎯 Project Roles Overview

Sabai uchun 5 ta asosiy rol o'rnatilgan:

| Role | O'zbek | Maqsad | Status |
|------|--------|--------|--------|
| **admin** | Admin | Tizimni to'liq boshqarish | ✅ Live |
| **dispatcher** | Dispetcher | Bronlarni boshqarish, Masterga tayinlash | ✅ Live |
| **master** | Master | Xizmatlarni taqdim etish | ✅ Live |
| **customer** | Xaridor | Xizmatlar book qilish | ✅ Live |
| **editor** | Redaktor | Kontentni boshqarish (legacy) | ✅ Live |
| **writer** | Yozuvchi | Kontent yaratish (legacy) | ✅ Live |

---

## 👤 Role Details

### 1. ADMIN (Admin)

**Tavsifi**: Tizimni to'liq boshqaradi

**Ruxsatlar**: Barcha ruxsatlar

**Vazifalar**:
- ✅ Admin panelini ko'rish
- ✅ Barcha foydalanuvchilarni boshqarish
- ✅ Masterlarni boshqarish
- ✅ Dispetcherlarni boshqarish
- ✅ Xizmatlarni boshqarish
- ✅ Bronlarni boshqarish
- ✅ To'lovlarni boshqarish
- ✅ Hisobotlarni ko'rish
- ✅ Sozlamalarni o'zgartirish

**Test Account**:
```
Email:    admin@example.com
Password: password
Role:     admin
```

---

### 2. DISPATCHER (Dispetcher)

**Tavsifi**: Bronlarni boshqaradi va Masterlarga tayinlaydi

**Asosiy Vazifalar**:
- 📅 Barcha bronlarni ko'rish
- 🔄 Bron statusini o'zgartirish
- 👨‍💼 Bronlarni Masterlarga tayinlash
- 📊 Statistika ko'rish
- 💰 To'lovlarni ko'rish

**Ruxsatlar**:
- view bookings
- view all bookings
- change booking status
- assign bookings
- view services
- view payments
- view ratings
- view master profile
- view user statistics

**Test Account**:
```
Email:    dispatcher@example.com
Password: password
Role:     dispatcher
```

**Frontend Features** (To be implemented):
- [ ] Dispatcher Dashboard
- [ ] Booking Management Panel
- [ ] Master Assignment Interface
- [ ] Statistics Dashboard
- [ ] Payment Tracking

---

### 3. MASTER (Master/Service Provider)

**Tavsifi**: Xizmatlarni taqdim etadi, Bronlarni qabul qiladi

**Asosiy Vazifalar**:
- 🔧 O'z xizmatlarini yaratish/tahrir qilish
- 📅 Bronlarni qabul qilish/rad qilish
- ✅ Bron statusini o'zgartirish
- 👤 O'z profilini boshqarish
- ⭐ Rating va sharhlarni ko'rish
- 💰 Topilgan pul va to'lovlarni ko'rish

**Ruxsatlar**:
- create services
- edit services
- view services
- view bookings
- edit bookings
- change booking status
- edit own profile
- view own profile
- edit master profile
- view ratings
- create ratings
- view payments

**Test Accounts**:
```
Master 1:
Email:    master1@example.com
Password: password
Name:     Jamshid - Mastershunoslik

Master 2:
Email:    master2@example.com
Password: password
Name:     Fatima - Salonistka

Master 3:
Email:    master3@example.com
Password: password
Name:     Abdullayev - Remondi
```

**Frontend Features** (To be implemented):
- [ ] Master Profile
- [ ] Service Management
- [ ] Service Gallery
- [ ] Booking History
- [ ] Earnings Dashboard
- [ ] Rating & Reviews
- [ ] Schedule Management

---

### 4. CUSTOMER (Xaridor)

**Tavsifi**: Xizmatlarni book qiladi, Rating beradi

**Asosiy Vazifalar**:
- 🔍 Xizmatlarni qidirish va ko'rish
- 📅 Xizmatlarni book qilish
- ❌ Bronlarni bekor qilish
- ⭐ Ratinglar va sharhlar berish
- 👤 O'z profilini boshqarish
- 💰 To'lov tarixini ko'rish

**Ruxsatlar**:
- view services
- create bookings
- view bookings
- cancel bookings
- edit own profile
- view own profile
- create ratings
- view ratings
- view payments

**Test Accounts**:
```
Customer 1:
Email:    customer1@example.com
Password: password
Name:     Mustafo - Xaridor

Customer 2:
Email:    customer2@example.com
Password: password
Name:     Guli - Xaridor

Customer 3:
Email:    customer3@example.com
Password: password
Name:     Aziz - Xaridor
```

**Frontend Features** (To be implemented):
- [ ] Service Search & Filter
- [ ] Service Details Page
- [ ] Booking Page
- [ ] Booking History
- [ ] Rating & Reviews
- [ ] Profile Management
- [ ] Payment Methods

---

## 🔐 Permissions Breakdown

### Service Management Permissions
```
- create services       (Master)
- edit services        (Master)
- delete services      (Admin)
- view services        (All authenticated)
- manage service categories (Admin)
```

### Booking Management Permissions
```
- create bookings            (Customer)
- edit bookings             (Master, Dispatcher)
- view bookings             (Owner, Master, Dispatcher, Admin)
- cancel bookings           (Customer)
- assign bookings           (Dispatcher, Admin)
- change booking status     (Master, Dispatcher, Admin)
- view all bookings         (Dispatcher, Admin)
```

### Profile Management Permissions
```
- edit own profile          (All authenticated)
- view own profile          (All authenticated)
- edit master profile       (Master, Admin)
- view master profile       (All authenticated)
```

### Rating & Review Permissions
```
- create ratings       (Customer, Master)
- view ratings         (All authenticated)
- manage ratings       (Admin)
```

### Payment Permissions
```
- view payments        (Owner, Master, Dispatcher, Admin)
- process payments     (Admin)
- refund payments      (Admin)
```

### User Management Permissions (Admin only)
```
- manage users             (Admin)
- manage masters           (Admin)
- manage dispatchers       (Admin)
- view user statistics     (Admin, Dispatcher)
```

### System Permissions (Admin only)
```
- view admin panel         (Admin)
- manage settings          (Admin)
- view reports             (Admin)
- manage payments          (Admin)
```

---

## 🔄 Role Workflow

### 1. Customer Books Service

```
Customer
  ↓
  Views Services
  ↓
  Creates Booking
  ↓
  Dispatcher Assigns to Master
  ↓
  Master Accepts/Rejects
  ↓
  Service Completed
  ↓
  Customer Rates Master
```

### 2. Master Manages Services

```
Master
  ↓
  Creates/Edits Service
  ↓
  Receives Booking
  ↓
  Changes Status (Accepted, In Progress, Completed)
  ↓
  Views Earnings
  ↓
  Views Customer Ratings
```

### 3. Dispatcher Manages Bookings

```
Dispatcher
  ↓
  Views All Bookings
  ↓
  Assigns to Available Master
  ↓
  Monitors Status
  ↓
  Changes Status if needed
  ↓
  Views Statistics
```

---

## 📊 User Count by Role

After seeding:

```
Total Users:    18
├── Admin:       1
├── Dispatcher:  1
├── Master:      3
├── Customer:    3
├── Editor:      1
├── Writer:      1
└── Unassigned: 8
```

---

## 🧪 Testing Roles

### Test All Roles

```bash
docker compose exec app php artisan tinker

# Check roles exist
>>> Role::all()

# Check admin user
>>> $admin = User::find(1)
>>> $admin->getRoleNames()
>>> $admin->hasRole('admin')

# Check dispatcher
>>> $dispatcher = User::where('email', 'dispatcher@example.com')->first()
>>> $dispatcher->hasRole('dispatcher')
>>> $dispatcher->can('assign bookings')

# Check master
>>> $master = User::where('email', 'master1@example.com')->first()
>>> $master->hasRole('master')
>>> $master->can('create services')

# Check customer
>>> $customer = User::where('email', 'customer1@example.com')->first()
>>> $customer->hasRole('customer')
>>> $customer->can('create bookings')
```

---

## 🛡️ Permission Checking Examples

### In Controller

```php
// Check single permission
if (auth()->user()->can('create services')) {
    // User can create services
}

// Check multiple permissions
if (auth()->user()->can('assign bookings')) {
    // Only dispatchers and admins
}

// Using policy
$this->authorize('create', Service::class);
```

### In Blade Template

```blade
{{-- Check if has role --}}
@role('dispatcher')
    <p>Dispetcher Dashboard</p>
@endrole

@hasrole('master')
    <p>Masterga ruxsat berildi</p>
@endhasrole

{{-- Check if has permission --}}
@can('assign bookings')
    <button>Bronni Tayinlash</button>
@endcan

@cannot('delete services')
    <p>Xizmatni o'chira olmaysiz</p>
@endcannot
```

### In Routes

```php
// Protect route with role
Route::middleware('role:master')->group(function () {
    Route::resource('services', MasterServiceController::class);
});

// Protect route with permission
Route::middleware('permission:assign bookings')->group(function () {
    Route::post('/bookings/{booking}/assign', [BookingController::class, 'assign']);
});

// Multiple roles
Route::middleware('role:master|dispatcher')->group(function () {
    Route::get('/bookings', [BookingController::class, 'index']);
});
```

---

## 🚀 Adding New Roles

### Example: Add 'supervisor' Role

```php
// In seeder
$supervisor = Role::create(['name' => 'supervisor']);

// Give permissions
$supervisor->givePermissionTo([
    'view bookings',
    'view all bookings',
    'view user statistics',
    'view reports',
]);

// Assign to user
$user->assignRole('supervisor');
```

### Clear Cache After Changes

```bash
php artisan permission:cache-reset
php artisan cache:clear
```

---

## 📝 Checklist: Implementing Role-Based Features

- [ ] Create controller for role-specific actions
- [ ] Add permission checks in controller
- [ ] Create role-specific views
- [ ] Add middleware for route protection
- [ ] Create policy for authorization
- [ ] Add blade directives in templates
- [ ] Test all role permissions
- [ ] Clear permission cache
- [ ] Update documentation

---

## 🎓 Best Practices

1. **Use Policies** for complex authorization logic
2. **Use Middleware** for route-level protection
3. **Check Permissions** in controllers before actions
4. **Use Blade Directives** in templates for UI hiding
5. **Clear Cache** after permission changes
6. **Test All Roles** before deployment
7. **Document Permissions** in code comments
8. **Log Admin Actions** for audit trail

---

## ⚠️ Important Notes

### Development
- All test passwords are 'password'
- Roles are cached for performance
- Clear cache when adding new roles/permissions

### Production
- [ ] Change default admin password
- [ ] Implement 2FA for admin/dispatcher
- [ ] Use strong password policies
- [ ] Enable role audit logging
- [ ] Monitor permission changes
- [ ] Regular permission review

---

## 🔗 Related Documentation

- `ADMIN_SETUP.md` - Admin panel guide
- `SPATIE_SETUP.md` - Permission system detailed guide
- `QUICK_REFERENCE.md` - Quick permission examples

---

## 📞 Permission Reference

**Total Permissions**: 42
**Total Roles**: 6
**Test Users**: 18

See `RoleAndPermissionSeeder.php` for complete permission definitions.
