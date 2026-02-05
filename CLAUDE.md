# HomeMessage - Massage Booking MVP

## Quick Overview

**Bu nima?** Massage booking MVP - mijozlar online buyurtma beradi, dispetcherlar boshqaradi, masterlar xizmat ko'rsatadi.

**Tech Stack:**
- Backend: Laravel 12.x + PHP 8.x
- Frontend: Vue 3 + Inertia.js + Tailwind CSS
- Database: MySQL 8.0 (Docker)
- Admin UI: AdminLTE style
- i18n: vue-i18n (uz, ru, en)

## Docker Commands

```bash
# Start containers
docker compose up -d

# Run artisan commands
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed

# Build frontend
npm run build
npm run dev
```

**Ports:**
- App: http://localhost:8081
- MySQL: localhost:3307 (external), 3306 (internal)

## Project Structure

```
app/
├── Models/
│   ├── User.php              # Admin/Dispatcher (Spatie Roles)
│   ├── ServiceType.php       # Massage types (translatable)
│   ├── Oil.php               # Oil options (translatable)
│   └── ...
├── Http/Controllers/Admin/   # Admin panel controllers
└── Services/                 # Business logic

resources/js/
├── i18n/locales/            # Translations (uz.json, ru.json, en.json)
├── Pages/Admin/             # Admin Vue pages
├── Components/Admin/        # Reusable components
└── Layouts/AdminLayout.vue  # Main admin layout
```

## Key Conventions

### 1. Translatable Models (Spatie)
```php
// Model
use Spatie\Translatable\HasTranslations;

class Oil extends Model {
    use HasTranslations;
    public $translatable = ['name', 'description'];
}

// Migration - JSON columns
$table->json('name');
$table->json('description')->nullable();

// Controller - save translations
$oil->setTranslation('name', 'uz', $request->input('uz.name'));
// or just pass array to create/update - it handles automatically
```

### 2. Vue i18n Usage
```javascript
import { useI18n } from 'vue-i18n';
const { t } = useI18n();

// Template
{{ t('oils.title') }}
{{ t('common.save') }}
```

### 3. AdminLTE Style Colors
```
Primary:   #007bff (blue)
Success:   #28a745 (green)
Warning:   #ffc107 (yellow)
Danger:    #dc3545 (red)
Info:      #17a2b8 (cyan)
Dark:      #343a40 (sidebar bg)
Gray:      #6c757d (secondary text)
Light:     #f8f9fa (backgrounds)
```

### 4. CRUD Page Pattern
```
Pages/Admin/{Resource}/
├── Index.vue    # List with pagination
├── Create.vue   # Form with tabs for translations
├── Edit.vue     # Form with tabs (yellow header)
└── Show.vue     # View with tabs
```

### 5. Controller Pattern
```php
// Store
$validated = $request->validate([...]);
Resource::create($validated);

// Update (with image handling)
if ($request->hasFile('image')) {
    Storage::disk('public')->delete($resource->image);
    $validated['image'] = $request->file('image')->store('resources', 'public');
}
$resource->update($validated);
```

## Current Models

| Model | Table | Translatable | Notes |
|-------|-------|--------------|-------|
| User | users | No | Spatie roles/permissions |
| ServiceType | service_types | name, description | Massage types |
| Oil | oils | name, description | Oil options |

## Admin Routes

```
/admin/dashboard          - Dashboard
/admin/service-types      - Massage types CRUD
/admin/oils               - Oil options CRUD
```

## Test Credentials

```
Email: admin@example.com
Password: password
```

## Guidelines Location

Batafsil ma'lumot `.ai/guidelines/project/` papkasida:
- `architecture.blade.php` - Full architecture docs
- `permissions.blade.php` - Roles & permissions
- `translatable.blade.php` - Translation setup
- `order-workflow.blade.php` - Order processing
- `slot-management.blade.php` - Slot logic

## Common Tasks

### Add New CRUD Resource
1. Create migration: `php artisan make:migration create_resources_table`
2. Create model with `HasTranslations` trait
3. Create controller in `app/Http/Controllers/Admin/`
4. Add routes in `routes/web.php`
5. Create Vue pages in `resources/js/Pages/Admin/Resources/`
6. Add sidebar menu item
7. Add translations to `i18n/locales/*.json`
8. Run `npm run build`

### Add New Translation Key
1. Add to `resources/js/i18n/locales/uz.json`
2. Add to `resources/js/i18n/locales/ru.json`
3. Add to `resources/js/i18n/locales/en.json`
4. Use with `t('key.subkey')` in Vue components
