## Clean Architecture Pattern - HomeMessage

This project follows a clean architecture pattern with clear separation of concerns.

---

### Layer Overview

| Layer | Location | Responsibility |
|-------|----------|----------------|
| **Controllers** | `app/Http/Controllers/` | HTTP layer - handle requests, return responses |
| **Form Requests** | `app/Http/Requests/` | Validation rules, input preparation |
| **Services** | `app/Services/` | Business logic, complex operations |
| **Repositories** | `app/Repositories/` | Data access, filtering, queries |
| **Models** | `app/Models/` | Eloquent ORM, relationships |

---

### 1. Controllers (Thin Controllers)

Controllers should ONLY handle:
- Receiving HTTP requests
- Calling services/repositories
- Returning responses (Inertia render or redirect)

```php
// app/Http/Controllers/Admin/MasterController.php

class MasterController extends Controller
{
    public function __construct(
        protected MasterService $masterService,
        protected MasterRepository $masterRepository,
        protected ServiceTypeRepository $serviceTypeRepository,
        protected OilRepository $oilRepository
    ) {}

    public function index(Request $request)
    {
        return Inertia::render('Admin/Masters/Index', [
            'masters' => $this->masterRepository->getFilteredPaginated($request->all()),
            'serviceTypes' => $this->serviceTypeRepository->getActive(),
            'filters' => $request->only(['search', 'status', 'gender']),
        ]);
    }

    public function store(StoreMasterRequest $request)
    {
        $this->masterService->create($request->validated(), $request);

        return redirect()->route('admin.masters.index')
            ->with('success', 'Master muvaffaqiyatli qo\'shildi');
    }

    public function update(UpdateMasterRequest $request, Master $master)
    {
        $this->masterService->update($master, $request->validated(), $request);

        return redirect()->route('admin.masters.index')
            ->with('success', 'Master muvaffaqiyatli yangilandi');
    }

    public function destroy(Master $master)
    {
        $this->masterService->delete($master);

        return redirect()->route('admin.masters.index')
            ->with('success', 'Master o\'chirildi');
    }
}
```

**Rules:**
- No direct model queries in controllers
- No validation logic in controllers
- No business logic in controllers
- Use constructor injection for dependencies

---

### 2. Form Requests (Validation)

All validation logic lives in Form Request classes.

```php
// app/Http/Requests/Admin/Master/StoreMasterRequest.php

namespace App\Http\Requests\Admin\Master;

use Illuminate\Foundation\Http\FormRequest;

class StoreMasterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // or check permissions
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|unique:masters',
            'email' => 'required|email|unique:masters|unique:users,email',
            'password' => 'required|string|min:6',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'birth_date' => 'nullable|date',
            'gender' => 'required|in:male,female',
            'experience_years' => 'required|integer|min:0|max:50',
            'status' => 'boolean',
            'service_types' => 'nullable|array',
            'service_types.*' => 'exists:service_types,id',
            'oils' => 'nullable|array',
            'oils.*' => 'exists:oils,id',
            'uz.bio' => 'nullable|string',
            'ru.bio' => 'nullable|string',
            'en.bio' => 'nullable|string',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->boolean('status'),
        ]);
    }
}
```

**Directory Structure:**
```
app/Http/Requests/Admin/
├── Master/
│   ├── StoreMasterRequest.php
│   └── UpdateMasterRequest.php
├── ServiceType/
│   ├── StoreServiceTypeRequest.php
│   └── UpdateServiceTypeRequest.php
├── Oil/
├── StandardItem/
├── Dispatcher/
├── Customer/
├── Profile/
└── Setting/
```

---

### 3. Services (Business Logic)

Services contain all business logic and complex operations.

```php
// app/Services/MasterService.php

namespace App\Services;

use App\Models\Master;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MasterService
{
    public function __construct(
        protected ImageService $imageService
    ) {}

    public function create(array $data, Request $request): Master
    {
        return DB::transaction(function () use ($data, $request) {
            // Handle image upload
            if ($request->hasFile('photo')) {
                $data['photo'] = $this->imageService->upload(
                    $request->file('photo'),
                    'masters'
                );
            }

            // Create user account for master
            $user = User::create([
                'name' => $data['first_name'] . ' ' . $data['last_name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'phone' => $data['phone'],
            ]);
            $user->assignRole('master');

            // Create master
            $master = Master::create([
                ...$data,
                'user_id' => $user->id,
            ]);

            // Sync relationships
            if (!empty($data['service_types'])) {
                $master->serviceTypes()->sync($data['service_types']);
            }
            if (!empty($data['oils'])) {
                $master->oils()->sync($data['oils']);
            }

            return $master;
        });
    }

    public function update(Master $master, array $data, Request $request): Master
    {
        // Handle image replacement
        if ($request->hasFile('photo')) {
            $data['photo'] = $this->imageService->replace(
                $master->photo,
                $request->file('photo'),
                'masters'
            );
        }

        $master->update($data);

        // Sync relationships
        if (isset($data['service_types'])) {
            $master->serviceTypes()->sync($data['service_types']);
        }
        if (isset($data['oils'])) {
            $master->oils()->sync($data['oils']);
        }

        return $master;
    }

    public function delete(Master $master): bool
    {
        // Delete image
        if ($master->photo) {
            $this->imageService->delete($master->photo);
        }

        // Delete user account
        if ($master->user) {
            $master->user->delete();
        }

        return $master->delete();
    }

    public function getEditData(Master $master): Master
    {
        return $master->load('serviceTypes', 'oils')->append([
            'service_type_ids' => $master->serviceTypes->pluck('id'),
            'oil_ids' => $master->oils->pluck('id'),
        ]);
    }
}
```

**Available Services:**
- `ImageService` - Image upload/delete/replace
- `MasterService` - Master CRUD with user account
- `ServiceTypeService` - Service type operations
- `OilService` - Oil operations
- `StandardItemService` - Standard item operations
- `DispatcherService` - Dispatcher user operations
- `CustomerService` - Customer operations
- `ProfileService` - Profile and password updates

---

### 4. Repositories (Data Access)

Repositories handle all database queries and filtering logic.

```php
// app/Repositories/BaseRepository.php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository
{
    protected Model $model;

    public function __construct()
    {
        $this->model = app($this->getModelClass());
    }

    abstract protected function getModelClass(): string;

    public function query(): Builder
    {
        return $this->model->newQuery();
    }

    protected function applySearch(Builder $query, ?string $search, array $columns): Builder
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search, $columns) {
            foreach ($columns as $column) {
                $q->orWhere($column, 'like', "%{$search}%");
            }
        });
    }

    protected function applyStatusFilter(Builder $query, ?string $status): Builder
    {
        if (empty($status)) {
            return $query;
        }

        return $query->where('status', $status === 'active');
    }

    public function paginate(Builder $query, int $perPage = 10): LengthAwarePaginator
    {
        return $query->paginate($perPage)->withQueryString();
    }
}
```

```php
// app/Repositories/MasterRepository.php

namespace App\Repositories;

use App\Models\Master;
use Illuminate\Pagination\LengthAwarePaginator;

class MasterRepository extends BaseRepository
{
    protected function getModelClass(): string
    {
        return Master::class;
    }

    public function getFilteredPaginated(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->query()->with('serviceTypes');

        // Apply search
        $this->applySearch($query, $filters['search'] ?? null, [
            'first_name', 'last_name', 'phone', 'email'
        ]);

        // Apply status filter
        $this->applyStatusFilter($query, $filters['status'] ?? null);

        // Apply gender filter
        if (!empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        // Apply service type filter
        if (!empty($filters['service_type'])) {
            $query->whereHas('serviceTypes', function ($q) use ($filters) {
                $q->where('service_types.id', $filters['service_type']);
            });
        }

        return $this->paginate($query->latest(), $perPage);
    }
}
```

**Repository Methods:**
- `getFilteredPaginated()` - Main list with filters
- `getActive()` - Active items for dropdowns
- `find()`, `findOrFail()` - Single item lookup

---

### 5. Dependency Injection

Laravel automatically resolves dependencies via the service container.

```php
// Constructor injection (recommended)
public function __construct(
    protected MasterService $masterService,
    protected MasterRepository $masterRepository,
) {}

// Method injection (for specific actions)
public function store(StoreMasterRequest $request, MasterService $service)
{
    $service->create($request->validated(), $request);
}
```

---

### Creating New CRUD Resource

1. **Migration** - Create database table
2. **Model** - Define fillable, casts, relationships
3. **Form Requests** - Store/Update validation
4. **Service** - Business logic (create, update, delete)
5. **Repository** - Filtering and queries
6. **Controller** - Thin controller with DI
7. **Routes** - Resource routes
8. **Vue Pages** - Index, Create, Edit, Show

---

### File Naming Conventions

| Type | Pattern | Example |
|------|---------|---------|
| Form Request | `{Action}{Model}Request` | `StoreMasterRequest` |
| Service | `{Model}Service` | `MasterService` |
| Repository | `{Model}Repository` | `MasterRepository` |
| Controller | `{Model}Controller` | `MasterController` |
