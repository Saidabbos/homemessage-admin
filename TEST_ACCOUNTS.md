# 🧪 HomeMessage - Test Accounts

## 📋 All Test Accounts

Ushbu hisoblar loyihani test qilish uchun mavjud.

**Default Password (Barcha hisoblar uchun)**: `password`

---

## 👨‍💼 ADMIN - Tizim Administrator

```
Email:    admin@example.com
Password: password
Role:     admin
```

**Huquqlar**: Barcha ruxsatlar
**Maqsadi**: Tizimni to'liq boshqarish

---

## 📞 DISPATCHER - Dispetcher

```
Email:    dispatcher@example.com
Password: password
Role:     dispatcher
Name:     Dispatcher User
```

**Huquqlar**:
- Barcha bronlarni ko'rish
- Bronlarni Masterlarga tayinlash
- Bron statusini o'zgartirish
- Statistika ko'rish

**Maqsadi**: Bronlarni boshqarish va Masterlarga tayinlash

---

## 🔧 MASTER - Xizmat Taqdimotchilari (3 ta)

### Master 1 - Mastershunoslik

```
Email:    master1@example.com
Password: password
Role:     master
Name:     Jamshid - Mastershunoslik
```

### Master 2 - Salonistka

```
Email:    master2@example.com
Password: password
Role:     master
Name:     Fatima - Salonistka
```

### Master 3 - Remondi

```
Email:    master3@example.com
Password: password
Role:     master
Name:     Abdullayev - Remondi
```

**Huquqlar**:
- Xizmatlarni yaratish/tahrir qilish
- Bronlarni qabul qilish/rad qilish
- Profilni boshqarish
- Rating ko'rish
- Topilgan pulni ko'rish

**Maqsadi**: Xizmatlarni taqdim etish va bronlarni boshqarish

---

## 👨‍💼 CUSTOMER - Xaridor (3 ta)

### Customer 1

```
Email:    customer1@example.com
Password: password
Role:     customer
Name:     Mustafo - Xaridor
```

### Customer 2

```
Email:    customer2@example.com
Password: password
Role:     customer
Name:     Guli - Xaridor
```

### Customer 3

```
Email:    customer3@example.com
Password: password
Role:     customer
Name:     Aziz - Xaridor
```

**Huquqlar**:
- Xizmatlarni ko'rish
- Xizmatlarni book qilish
- Bronlarni bekor qilish
- Rating berish
- Profilni boshqarish

**Maqsadi**: Xizmatlarni book qilish va rating berish

---

## 📝 LEGACY ROLES (Eski rollalar)

### Editor

```
Email:    editor@example.com
Password: password
Role:     editor
```

### Writer

```
Email:    writer@example.com
Password: password
Role:     writer
```

---

## 🧪 Testing Guide

### 1. Admin Panel Test

```
URL: http://localhost/admin/login
Email: admin@example.com
Password: password
```

✅ Admin panelini ko'ring
✅ Dashboard statistikasini tekshiring
✅ Sidebar navigation ishlasin

### 2. Test Each Role

#### Admin Role
- [ ] Admin paneliga kirish
- [ ] Dashboard ko'ring
- [ ] Barcha menyular accessible

#### Dispatcher Role
- [ ] Dispetcher bilan login
- [ ] Bronlarni ko'ring
- [ ] Statistika ko'ring

#### Master Role
- [ ] Master bilan login
- [ ] Xizmatlarni yaratish
- [ ] Bronlarni ko'ring
- [ ] Profilni tahrir qilish

#### Customer Role
- [ ] Customer bilan login
- [ ] Xizmatlarni qidirish
- [ ] Booking yaratish
- [ ] Rating berish

---

## 🔐 Permission Testing Examples

### Test Admin Permissions

```bash
docker compose exec app php artisan tinker

>>> $admin = User::find(1)
>>> $admin->hasRole('admin')  # true
>>> $admin->can('manage users')  # true
>>> $admin->can('view admin panel')  # true
```

### Test Dispatcher Permissions

```bash
>>> $dispatcher = User::where('email', 'dispatcher@example.com')->first()
>>> $dispatcher->hasRole('dispatcher')  # true
>>> $dispatcher->can('assign bookings')  # true
>>> $dispatcher->can('manage users')  # false
```

### Test Master Permissions

```bash
>>> $master = User::where('email', 'master1@example.com')->first()
>>> $master->hasRole('master')  # true
>>> $master->can('create services')  # true
>>> $master->can('assign bookings')  # false
```

### Test Customer Permissions

```bash
>>> $customer = User::where('email', 'customer1@example.com')->first()
>>> $customer->hasRole('customer')  # true
>>> $customer->can('create bookings')  # true
>>> $customer->can('create services')  # false
```

---

## 📊 Quick Reference

| Account | Email | Role | Focus Area |
|---------|-------|------|-----------|
| Admin | admin@example.com | admin | System Management |
| Dispatcher | dispatcher@example.com | dispatcher | Booking Assignment |
| Master 1 | master1@example.com | master | Mastershunoslik |
| Master 2 | master2@example.com | master | Beauty Services |
| Master 3 | master3@example.com | master | Home Repairs |
| Customer 1 | customer1@example.com | customer | General |
| Customer 2 | customer2@example.com | customer | General |
| Customer 3 | customer3@example.com | customer | General |
| Editor | editor@example.com | editor | Content |
| Writer | writer@example.com | writer | Content |

---

## 🎯 Testing Workflow

### Workflow 1: Complete Booking Process

1. **Customer**: customer1@example.com
   - Login
   - View services
   - Create booking

2. **Dispatcher**: dispatcher@example.com
   - Login
   - See new booking
   - Assign to master1

3. **Master**: master1@example.com
   - Login
   - Accept booking
   - Change status to "In Progress"
   - Change status to "Completed"

4. **Customer**: customer1@example.com
   - See booking completed
   - Give rating (5 stars)

### Workflow 2: Service Management

1. **Master**: master2@example.com
   - Login
   - Create new service
   - Edit service details
   - Upload photos

2. **Dispatcher**: dispatcher@example.com
   - See new service available
   - Monitor master's bookings

3. **Customer**: customer2@example.com
   - Find master2's service
   - View details
   - Make booking

---

## 💡 Pro Tips

1. **Password**: Hamma hisoblar uchun `password` (default Laravel factory)
2. **Role Check**: `php artisan tinker` da rollarni tekshiring
3. **Clear Cache**: Yangi role qo'shsangiz: `php artisan permission:cache-reset`
4. **Log Output**: Database changes ko'rish uchun logs tekshiring
5. **Create Users**: Yangi test user yaratish: `php artisan tinker` → `User::factory()->create()`

---

## 🚀 Next Steps

1. ✅ Test barcha rollarni
2. ✅ Frontend va Controller yaratish
3. ✅ Role-based views qo'shish
4. ✅ Permission checks qo'shish
5. ✅ Database modelsni yaratish

---

## ❓ Troubleshooting

### "User can't access something"
```bash
# Check role
>>> $user = User::find(id)
>>> $user->getRoleNames()

# Check permission
>>> $user->hasPermissionTo('permission_name')

# Clear cache
php artisan permission:cache-reset
```

### "Login not working"
- Email topilgan-yo'qligini tekshiring
- Password `password` ekanligini verify qiling
- Role assigned ekanligini tekshiring

### "Need new test user"
```bash
>>> User::factory()->create(['email' => 'test@test.com'])->assignRole('customer')
```

---

## 📞 Quick Commands

```bash
# List all users
docker compose exec app php artisan tinker
>>> User::all()

# List all roles
>>> Role::all()

# List all permissions
>>> Permission::all()

# Check user roles
>>> User::find(1)->getRoleNames()

# Clear permission cache
docker compose exec app php artisan permission:cache-reset

# Reset database and seed
docker compose exec app php artisan migrate:refresh --seed
```

---

Hozir test qilishga tayinnsiz! 🚀
