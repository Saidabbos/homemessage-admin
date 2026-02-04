# Admin Panel Setup - HomeMessage

## 🔐 Admin Authentication System

Admin panel HomeMessage uchun alohida authentication sistema bilan sozlangan.

---

## 📋 Admin Hisob

### Default Admin Account

```
Email:    admin@example.com
Password: password
Role:     admin
Status:   Active
```

### Login URL

```
http://localhost/admin/login
```

---

## 🛣️ Admin Routes

### Auth Routes (Public)
```
GET  /admin/login             - Admin login page
POST /admin/login             - Login submit
POST /admin/logout            - Logout (protected)
```

### Protected Routes (Admin Only)
```
GET  /admin/dashboard         - Admin dashboard
GET  /admin/users             - Users management (to be created)
GET  /admin/services          - Services management (to be created)
GET  /admin/bookings          - Bookings management (to be created)
GET  /admin/reports           - Reports & Analytics (to be created)
GET  /admin/settings          - System settings (to be created)
```

---

## 🏗️ Project Structure

### Controllers
```
app/Http/Controllers/Admin/
├── AuthController.php         # Login/Logout
├── DashboardController.php    # Dashboard stats
├── UserController.php         # (To be created)
├── ServiceController.php      # (To be created)
├── BookingController.php      # (To be created)
└── ReportController.php       # (To be created)
```

### Middleware
```
app/Http/Middleware/
└── AdminMiddleware.php        # Check admin role
```

### Views
```
resources/views/admin/
├── auth/
│   ├── login.blade.php
│   └── layout.blade.php
├── dashboard/
│   ├── index.blade.php
│   ├── users/
│   ├── services/
│   ├── bookings/
│   └── reports/
└── layouts/
    └── app.blade.php          # Main admin layout
```

### Routes
```
routes/web.php
├── /admin/login               (Public)
└── /admin/* routes            (Admin protected)
```

---

## 🔑 How Admin Authentication Works

### 1. Login Process

**User navigates to**: `http://localhost/admin/login`

**Form submission** (`POST /admin/login`):
- Email validation
- Password validation
- User verification
- Admin role check
- Session creation

**Redirect**:
- Success → Dashboard
- Failure → Login page with error

### 2. Session Protection

**Middleware**: `AdminMiddleware` checks:
1. User is authenticated
2. User has 'admin' role
3. If not → 403 Forbidden

### 3. Logout Process

**Route**: `POST /admin/logout`
- Session destroyed
- Redirects to login page

---

## 🎨 Login Page

### URL
```
http://localhost/admin/login
```

### Features
- ✅ Email/Password form
- ✅ "Remember me" checkbox
- ✅ Error messages
- ✅ Responsive design
- ✅ Demo credentials display
- ✅ Modern UI with gradient

### Style
- Gradient background (Purple)
- Card-based layout
- Form validation
- Accessibility focused

---

## 📊 Dashboard

### URL
```
http://localhost/admin/dashboard
```

### Features
- ✅ User statistics (Total, Admins, Editors, Writers)
- ✅ Quick action buttons
- ✅ Recent activity (placeholder)
- ✅ Responsive grid layout

### Statistics Shown
- Total Users
- Total Admins
- Total Editors
- Total Writers

---

## 🎯 Admin Features

### Current Features (Implemented)
- ✅ Admin login/logout
- ✅ Admin dashboard
- ✅ Role verification
- ✅ Session management
- ✅ Admin middleware protection
- ✅ Responsive admin layout

### Planned Features
- [ ] User management (CRUD)
- [ ] User role assignment
- [ ] Service management
- [ ] Booking management
- [ ] Reports & Analytics
- [ ] System settings
- [ ] Admin activity log

---

## 🔧 Using Admin Panel

### 1. Access Login Page

```
Navigate to: http://localhost/admin/login
```

### 2. Enter Credentials

```
Email:    admin@example.com
Password: password
```

### 3. View Dashboard

After successful login, you'll see:
- Dashboard with statistics
- Quick action buttons
- Sidebar navigation
- User menu

### 4. Logout

Click the user menu (top right) → Logout

---

## 🛡️ Security Features

### Password Protection
- `@csrf` token protection
- Password field type
- Server-side validation

### Role-Based Access
- Admin role required
- Middleware verification
- Session-based authentication

### Error Handling
- Invalid email/password
- User not admin
- Missing credentials
- Session timeout

### Best Practices
- Use strong password
- Regular password changes (implement later)
- Monitor admin activity (implement later)
- 2FA support (future)

---

## 📱 Responsive Design

### Desktop
- Full sidebar navigation
- Grid-based layout
- Full-width content

### Tablet (768px)
- Sidebar visible
- 2-column stats grid
- Touch-friendly buttons

### Mobile (600px)
- Compact sidebar
- Full-width single column
- Stack layout

---

## 🚀 Adding New Admin Features

### 1. Create Controller

```bash
docker compose exec app php artisan make:controller Admin/YourController
```

### 2. Add Routes

In `routes/web.php`:
```php
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::resource('resources', Admin\YourController::class);
});
```

### 3. Create Views

```
resources/views/admin/resources/
├── index.blade.php
├── create.blade.php
├── edit.blade.php
└── show.blade.php
```

### 4. Use Layout

Extend admin layout:
```blade
@extends('admin.layouts.app')

@section('title', 'Resource Name')

@section('content')
    <!-- Your content -->
@endsection
```

---

## 🧪 Testing Admin Login

### Manual Testing

1. **Open Browser**
   ```
   http://localhost/admin/login
   ```

2. **Enter Credentials**
   ```
   Email: admin@example.com
   Password: password
   ```

3. **Click Login**

4. **Verify Dashboard Loads**

5. **Check User Menu**
   - Click user avatar
   - See dropdown menu
   - Click logout

### Artisan Testing

```bash
# Check routes
docker compose exec app php artisan route:list

# Tinker testing
docker compose exec app php artisan tinker

# Check user role
>>> User::find(1)->hasRole('admin')
>>> User::find(1)->getRoleNames()
```

---

## 📝 Admin Middleware

### File Location
```
app/Http/Middleware/AdminMiddleware.php
```

### What It Does
1. Checks if user is authenticated
2. Checks if user has 'admin' role
3. Returns 403 if not admin
4. Redirects to login if not authenticated

### Usage in Routes

```php
Route::middleware(['auth', 'admin'])->group(function () {
    // Protected routes
});
```

---

## 🔄 Admin Session Management

### Session Configuration
- Driver: file (default)
- Lifetime: 120 minutes
- Secure: false (development)

### Production Recommendations
- Use database sessions
- Enable session encryption
- Set secure cookie flag
- Use HTTPS only

---

## 🎛️ Configuration

### Admin Settings (To be created)
- Site name
- Logo/Branding
- Admin email
- Timezone
- Language preferences

### Database Settings (Current)
- Connection: MySQL
- Host: mysql container
- Database: laravel
- User: laravel_user

---

## ⚠️ Important Notes

### Development
- Default admin account enabled
- No 2FA
- Session in files

### Before Production
- [ ] Change admin password
- [ ] Enable HTTPS
- [ ] Use database sessions
- [ ] Add 2FA
- [ ] Add rate limiting
- [ ] Set secure cookies
- [ ] Monitor admin access

### Admin Best Practices
1. Use strong password
2. Limited admin accounts
3. Regular backups
4. Activity logs
5. IP whitelisting (optional)

---

## 📊 Admin Database Tables Used

### users
- id
- name
- email
- password
- created_at, updated_at

### model_has_roles
- model_id, model_type
- role_id
(Links users to admin role)

### roles
- id, name, guard_name, created_at, updated_at
(Contains 'admin' role)

---

## 🆘 Troubleshooting

### "Email or password is incorrect"
- Check credentials
- Verify admin account exists
- Check user hasn't been deleted

### "You don't have admin account"
- User exists but role missing
- Assign admin role in database:
  ```sql
  INSERT INTO model_has_roles
  VALUES (1, 'App\\Models\\User', role_id);
  ```

### Middleware error
- Check AdminMiddleware registered in bootstrap/app.php
- Verify routes use 'admin' middleware
- Clear cache: `php artisan cache:clear`

### Login page not loading
- Check route registered
- Verify view file exists
- Check view syntax

### Dashboard blank
- Clear cache
- Check User model accessible
- Verify role relationship

---

## 📞 Support

For issues:
1. Check logs: `docker compose logs app`
2. Tinker testing: `php artisan tinker`
3. Review routes: `php artisan route:list`
4. Check permissions: `php artisan permission:cache-reset`

---

## ✨ Next Steps

1. **Create User Management**
   - List users
   - Edit user roles
   - Delete users
   - Ban/Suspend users

2. **Create Service Management**
   - Add services
   - Edit services
   - Category management

3. **Create Booking Management**
   - View bookings
   - Change status
   - Handle disputes

4. **Create Reports**
   - Revenue reports
   - User statistics
   - Service popularity

5. **Add Audit Logging**
   - Track admin actions
   - Login history
   - Change history
