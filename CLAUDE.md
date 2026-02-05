# HomeMessage - Massage Booking MVP

## Quick Overview

**Bu nima?** Massage booking MVP - mijozlar online buyurtma beradi, dispetcherlar boshqaradi, masterlar xizmat ko'rsatadi.

**Tech Stack:**
- Backend: Laravel 12.x + PHP 8.x
- Frontend: Vue 3 + Inertia.js + Tailwind CSS
- Database: MySQL 8.0 (Docker)
- Admin UI: AdminLTE style
- i18n: vue-i18n (uz, ru, en)

## Sprint Progress

| Sprint | Focus | Status |
|--------|-------|--------|
| Sprint 1 | Foundation + Admin CRUD | ✅ In Progress |
| Sprint 2 | Public Booking + Telegram | ⏳ Pending |
| Sprint 3 | Admin Orders + Payments | ⏳ Pending |
| Sprint 4 | Customer Portal + QA | ⏳ Pending |

**Completed (Sprint 1):**
- ✅ ServiceTypes, Oils, StandardItems CRUD
- ✅ Masters CRUD (with service types, oils relations)
- ✅ Dispatchers, Customers management
- ✅ Settings, Profile pages
- ✅ Clean Architecture (Services, Repositories, Form Requests)

**Next Steps:**
- Slots CRUD va management
- Orders system
- Payments integration (Payme/Click)

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
├── Models/                       # Eloquent models
│   ├── User.php                  # Admin/Dispatcher (Spatie Roles)
│   ├── Master.php                # Therapists (with user account)
│   ├── ServiceType.php           # Massage types (translatable)
│   ├── Oil.php                   # Oil options (translatable)
│   ├── StandardItem.php          # Standard items
│   └── Setting.php               # App settings (cached)
├── Http/
│   ├── Controllers/Admin/        # Thin controllers (HTTP layer only)
│   └── Requests/Admin/           # Form Request validation classes
├── Services/                     # Business logic layer
│   ├── ImageService.php          # Image upload/delete
│   ├── MasterService.php         # Master CRUD logic
│   └── ...
└── Repositories/                 # Data access layer
    ├── BaseRepository.php        # Abstract base class
    ├── MasterRepository.php      # Master queries & filters
    └── ...

resources/js/
├── i18n/locales/                 # Translations (uz.json, ru.json, en.json)
├── Pages/Admin/                  # Admin Vue pages
├── Components/Admin/             # Reusable components
└── Layouts/AdminLayout.vue       # Main admin layout

sprints/                          # Sprint task files
├── sprint-1-tasks.md             # Foundation + APIs
├── sprint-2-tasks.md             # Public Booking + Telegram
├── sprint-3-tasks.md             # Admin + Payments
└── sprint-4-tasks.md             # Customer Portal + QA
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

### 5. Clean Architecture Pattern

**Layer Responsibilities:**
| Layer | Location | Responsibility |
|-------|----------|----------------|
| Controllers | `Http/Controllers/` | HTTP layer - routing, responses |
| Form Requests | `Http/Requests/` | Validation, input preparation |
| Services | `Services/` | Business logic, CRUD operations |
| Repositories | `Repositories/` | Data access, filtering, queries |

**Controller Pattern (Thin Controller):**
```php
class MasterController extends Controller
{
    public function __construct(
        protected MasterService $masterService,
        protected MasterRepository $masterRepository,
    ) {}

    public function index(Request $request)
    {
        return Inertia::render('Admin/Masters/Index', [
            'masters' => $this->masterRepository->getFilteredPaginated($request->all()),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function store(StoreMasterRequest $request)
    {
        $this->masterService->create($request->validated(), $request);
        return redirect()->route('admin.masters.index')
            ->with('success', 'Master muvaffaqiyatli qo\'shildi');
    }
}
```

**Form Request Pattern:**
```php
class StoreMasterRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'phone' => 'required|string|unique:masters',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }
}
```

**Service Pattern:**
```php
class MasterService
{
    public function __construct(protected ImageService $imageService) {}

    public function create(array $data, Request $request): Master
    {
        if ($request->hasFile('photo')) {
            $data['photo'] = $this->imageService->upload($request->file('photo'), 'masters');
        }
        return Master::create($data);
    }
}
```

**Repository Pattern:**
```php
class MasterRepository extends BaseRepository
{
    protected function getModelClass(): string { return Master::class; }

    public function getFilteredPaginated(array $filters, int $perPage = 10)
    {
        $query = $this->query()->with('serviceTypes');
        $this->applySearch($query, $filters['search'] ?? null, ['first_name', 'last_name']);
        $this->applyStatusFilter($query, $filters['status'] ?? null);
        return $this->paginate($query->latest(), $perPage);
    }
}
```

## Current Models

| Model | Table | Translatable | Notes |
|-------|-------|--------------|-------|
| User | users | No | Spatie roles/permissions |
| Master | masters | bio | Therapists with user account |
| ServiceType | service_types | name, description | Massage types |
| Oil | oils | name, description | Oil options |
| StandardItem | standard_items | name, description | Standard equipment |
| Setting | settings | No | Cached app settings |

## Admin Routes

```
/admin/dashboard          - Dashboard
/admin/masters            - Masters CRUD
/admin/service-types      - Massage types CRUD
/admin/oils               - Oil options CRUD
/admin/standard-items     - Standard items CRUD
/admin/dispatchers        - Dispatcher users
/admin/customers          - Customer management
/admin/settings           - App settings
/admin/profile            - Admin profile
```

## Business Rules

- **Order Number Format:** `GT-YYYYMMDD-XXX`
- **6-hour rule:** 6 soatdan kam vaqt qolgan slotlar unavailable
- **Slot Status Flow:** `FREE → PENDING → RESERVED`
- **Order Status:** `NEW → CONFIRMING → WAITING_PAYMENT → PAID → RESERVED → COMPLETED`
- **Lead time:** Minimum 2 soat oldindan buyurtma

## Test Credentials

```
Email: admin@example.com
Password: password
```

## Guidelines Location

Batafsil ma'lumot `.ai/guidelines/project/` papkasida:
- `architecture.blade.php` - Full architecture docs
- `clean-architecture.blade.php` - Services, Repositories, Form Requests
- `permissions.blade.php` - Roles & permissions
- `translatable.blade.php` - Translation setup
- `order-workflow.blade.php` - Order processing
- `slot-management.blade.php` - Slot logic
- `golden-touch.blade.php` - Business logic & flows

**Skills** (`.ai/skills/`):
- `order-processing/` - Order lifecycle management
- `payment-integration/` - Payme/Click setup
- `telegram-notifications/` - Telegram bot integration

**Sprint Plans** (`sprints/`):
- sprint-1 through sprint-4 task breakdowns

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
