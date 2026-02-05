## Golden Touch - Permissions & Roles System

This project uses **spatie/laravel-permission** for access control in the admin panel.

### Roles Overview

| Role | Access | Description |
|------|--------|-------------|
| **admin** | Full system access | System configuration, user management, all reports |
| **owner** | Operations oversight | View all orders, dispatchers, masters, reports |
| **dispatcher** | Order management | Process orders, manage slots, send work orders |

Note: **Clients** and **Therapists (Masters)** are separate entities, not User roles.

---

### Current Permissions

#### Order Management
| Permission | Description | Roles |
|------------|-------------|-------|
| `view orders` | View order list and details | admin, owner, dispatcher |
| `create orders` | Create orders manually | admin, dispatcher |
| `edit orders` | Edit order details | admin, dispatcher |
| `delete orders` | Delete/cancel orders | admin |
| `confirm orders` | Fill confirmation form | admin, dispatcher |
| `manage payments` | Create invoices, confirm payments | admin, dispatcher |
| `send work orders` | Generate and send work orders to therapists | admin, dispatcher |
| `fill qa` | Fill quality control forms | admin, dispatcher |
| `export orders` | Export orders to CSV | admin, owner |

#### Slot Management
| Permission | Description | Roles |
|------------|-------------|-------|
| `view slots` | View slot schedules | admin, owner, dispatcher |
| `manage slots` | Create/edit/block slots | admin, dispatcher |

#### Therapist Management
| Permission | Description | Roles |
|------------|-------------|-------|
| `view therapists` | View therapist list | admin, owner, dispatcher |
| `manage therapists` | Create/edit/deactivate therapists | admin |
| `regenerate tokens` | Regenerate public tokens | admin |

#### Client Management
| Permission | Description | Roles |
|------------|-------------|-------|
| `view clients` | View client list | admin, owner, dispatcher |
| `manage clients` | Edit client details | admin, dispatcher |

#### System Configuration
| Permission | Description | Roles |
|------------|-------------|-------|
| `manage users` | Create/edit admin users | admin |
| `manage roles` | Assign roles to users | admin |
| `view reports` | Access analytics/reports | admin, owner |
| `manage settings` | System configuration | admin |

---

### Role-Permission Matrix

```php
// RoleAndPermissionSeeder.php

$adminPermissions = Permission::all();

$ownerPermissions = [
    'view orders', 'export orders',
    'view slots',
    'view therapists',
    'view clients',
    'view reports',
];

$dispatcherPermissions = [
    'view orders', 'create orders', 'edit orders', 'confirm orders',
    'manage payments', 'send work orders', 'fill qa',
    'view slots', 'manage slots',
    'view therapists',
    'view clients', 'manage clients',
];
```

---

### Implementation Patterns

#### Adding New Permissions
```php
// In seeder or artisan command
use Spatie\Permission\Models\Permission;

Permission::create(['name' => 'view analytics']);
Permission::create(['name' => 'manage promotions']);
```

#### Adding New Roles
```php
use Spatie\Permission\Models\Role;

$role = Role::create(['name' => 'supervisor']);
$role->givePermissionTo([
    'view orders', 'confirm orders', 'fill qa',
    'view slots', 'view therapists', 'view clients',
]);
```

#### Assigning Roles to Users
```php
// Single role
$user->assignRole('dispatcher');

// Multiple roles
$user->assignRole(['dispatcher', 'owner']);

// Sync (replaces all)
$user->syncRoles(['dispatcher']);
```

---

### Authorization in Controllers

#### Using Middleware
```php
// routes/web.php
Route::middleware(['auth', 'role:admin|dispatcher'])->prefix('admin')->group(function () {
    Route::get('/orders', [OrderController::class, 'index']);
});

Route::middleware(['auth', 'permission:manage payments'])->group(function () {
    Route::post('/orders/{order}/invoice', [OrderController::class, 'createInvoice']);
});
```

#### Using authorize() in Controllers
```php
class OrderController extends Controller
{
    public function index()
    {
        $this->authorize('view orders');
        return Order::with(['client', 'therapist'])->paginate();
    }

    public function confirmPayment(Order $order)
    {
        $this->authorize('manage payments');
        // Process payment confirmation...
    }

    public function fillQA(Order $order, Request $request)
    {
        $this->authorize('fill qa');
        // Fill quality assessment...
    }
}
```

#### Using Policies
```php
// app/Policies/OrderPolicy.php
class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        return $user->can('view orders');
    }

    public function confirm(User $user, Order $order): bool
    {
        return $user->can('confirm orders');
    }

    public function managePayment(User $user, Order $order): bool
    {
        return $user->can('manage payments');
    }

    public function sendWorkOrder(User $user, Order $order): bool
    {
        return $user->can('send work orders') &&
               $order->payment_status === 'PAID' &&
               $order->slot->status === 'RESERVED';
    }

    public function fillQA(User $user, Order $order): bool
    {
        return $user->can('fill qa') &&
               $order->status === 'RESERVED';
    }
}
```

---

### Blade/Vue Authorization

#### In Blade Templates
```blade
@can('manage payments')
    <button @click="createInvoice">Create Invoice</button>
@endcan

@role('admin')
    <a href="/admin/settings">Settings</a>
@endrole

@canany(['confirm orders', 'manage payments'])
    <div class="order-actions">...</div>
@endcanany
```

#### In Vue Components (Inertia)
```vue
<script setup>
// Permissions passed via HandleInertiaRequests middleware
const page = usePage()
const can = (permission) => page.props.auth.permissions.includes(permission)
const hasRole = (role) => page.props.auth.roles.includes(role)
</script>

<template>
    <button v-if="can('manage payments')" @click="createInvoice">
        Create Invoice
    </button>

    <div v-if="hasRole('admin')" class="admin-section">
        <!-- Admin only content -->
    </div>
</template>
```

#### In HandleInertiaRequests Middleware
```php
// app/Http/Middleware/HandleInertiaRequests.php
public function share(Request $request): array
{
    return array_merge(parent::share($request), [
        'auth' => [
            'user' => $request->user(),
            'permissions' => $request->user()?->getAllPermissions()->pluck('name') ?? [],
            'roles' => $request->user()?->getRoleNames() ?? [],
        ],
    ]);
}
```

---

### Common Operations

```php
// Check permission
if (auth()->user()->can('manage payments')) {
    // Allow payment management
}

// Check role
if (auth()->user()->hasRole('admin')) {
    // Admin-only operations
}

// Check any role
if (auth()->user()->hasAnyRole(['admin', 'dispatcher'])) {
    // Admin or dispatcher operations
}

// Get all user permissions
$permissions = auth()->user()->getAllPermissions();

// Get user roles
$roles = auth()->user()->getRoleNames(); // ['dispatcher']
```

---

### Database Tables

**roles** table
```sql
id | name       | guard_name | created_at | updated_at
1  | admin      | web        | ...        | ...
2  | owner      | web        | ...        | ...
3  | dispatcher | web        | ...        | ...
```

**permissions** table
```sql
id | name            | guard_name | created_at | updated_at
1  | view orders     | web        | ...        | ...
2  | manage payments | web        | ...        | ...
```

**role_has_permissions** - Links roles to permissions
**model_has_roles** - Links users to roles
**model_has_permissions** - Direct user permissions (rarely used)

---

### Best Practices

1. **Use descriptive names**: `manage payments` not just `payments`
2. **Prefer roles for grouping**: Assign permissions to roles, not directly to users
3. **Clear cache after changes**: `php artisan permission:cache-reset`
4. **Use policies for complex logic**: When authorization depends on model state
5. **Check in service layer**: Critical operations should verify permissions in services
6. **Log permission denials**: For security auditing

---

### Testing Permissions

```php
// tests/Feature/OrderPermissionTest.php
public function test_dispatcher_can_confirm_orders()
{
    $user = User::factory()->create();
    $user->assignRole('dispatcher');

    $order = Order::factory()->create();

    $this->actingAs($user)
        ->post("/admin/orders/{$order->id}/confirm", [...])
        ->assertStatus(200);
}

public function test_owner_cannot_confirm_orders()
{
    $user = User::factory()->create();
    $user->assignRole('owner');

    $order = Order::factory()->create();

    $this->actingAs($user)
        ->post("/admin/orders/{$order->id}/confirm", [...])
        ->assertStatus(403);
}
```
