## Permissions & Roles System

This project uses **spatie/laravel-permission** for fine-grained access control.

### Current Roles

| Role | Permissions | Use Case |
|------|-------------|----------|
| **admin** | All permissions | System administrators |
| **editor** | Create/Edit/Delete Posts, Manage Categories | Content managers |
| **writer** | Create/Edit Posts only | Content creators |

### Current Permissions

| Permission | Description |
|------------|-------------|
| `create posts` | Create new blog posts |
| `edit posts` | Edit existing posts |
| `delete posts` | Delete posts |
| `view posts` | View published posts |
| `create categories` | Create post categories |
| `edit categories` | Edit categories |
| `delete categories` | Delete categories |

### Adding New Permissions

```php
// In seeder or artisan command
Permission::create(['name' => 'publish posts']);
Permission::create(['name' => 'manage users']);

// Or with guard
Permission::create(['name' => 'moderate comments', 'guard_name' => 'web']);
```

### Adding New Roles

```php
$role = Role::create(['name' => 'moderator']);
$role->givePermissionTo(['create posts', 'edit posts', 'delete posts']);
```

### Assigning Roles to Users

```php
// Single role
$user->assignRole('editor');

// Multiple roles
$user->assignRole(['editor', 'moderator']);

// Sync (replaces all)
$user->syncRoles(['editor']);

// Give without removing others
$user->assignRole('moderator');
```

### Checking Permissions

```php
// Instance methods
$user->hasRole('admin');                    // true/false
$user->hasAnyRole(['admin', 'editor']);     // true/false
$user->hasAllRoles(['admin', 'editor']);    // true/false
$user->hasPermissionTo('create posts');     // true/false
$user->can('delete posts');                 // true/false

// Can shorthand
auth()->user()->can('edit posts');

// Collection methods
$user->getRoleNames();      // ['admin', 'editor']
$user->getAllPermissions(); // Collection of all permissions
```

### Middleware Protection

```php
// Protect routes with role
Route::middleware('role:admin')->group(function () {
    Route::get('/admin/users', [UserController::class, 'index']);
});

// Protect routes with permission
Route::middleware('permission:edit posts')->group(function () {
    Route::put('/posts/{post}', [PostController::class, 'update']);
});

// Multiple roles (OR logic)
Route::middleware('role:admin|moderator')->group(function () {
    Route::delete('/posts/{post}', [PostController::class, 'destroy']);
});

// Multiple permissions (AND logic)
Route::middleware('permission:create posts,edit posts')->group(function () {
    Route::post('/posts', [PostController::class, 'store']);
});
```

### Blade Template Checks

```blade
{{-- Single role check --}}
@role('admin')
    <p>Admin content</p>
@endrole

{{-- Multiple roles (OR) --}}
@hasrole('editor|admin')
    <p>Editor or Admin</p>
@endhasrole

{{-- Permission check --}}
@can('delete posts')
    <button>Delete</button>
@endcan

{{-- Multiple permissions --}}
@canany(['create posts', 'edit posts'])
    <p>Can create or edit</p>
@endcanany

{{-- Cannot check --}}
@cannot('delete posts')
    <p>You cannot delete posts</p>
@endcannot
```

### Authorization Policies

```php
class PostPolicy
{
    public function create(User $user): bool
    {
        return $user->can('create posts');
    }

    public function update(User $user, Post $post): bool
    {
        return $user->can('edit posts');
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->can('delete posts');
    }
}

// Register in AuthServiceProvider
protected $policies = [
    Post::class => PostPolicy::class,
];

// Use in controller
$this->authorize('create', Post::class);
$this->authorize('update', $post);
```

### Common Operations

#### Check if user can perform action
```php
if (auth()->user()->can('edit posts')) {
    // Allow editing
}
```

#### Assign role to user
```php
$user->assignRole('editor');
```

#### Give permission directly to user
```php
$user->givePermissionTo('edit posts');
```

#### Remove role
```php
$user->removeRole('editor');
```

#### Revoke permission
```php
$user->revokePermissionTo('delete posts');
```

#### Sync roles (replace all)
```php
$user->syncRoles(['admin', 'editor']); // Only has these roles
```

### Database Tables

**roles** table - Defines available roles
```sql
id | name | guard_name | created_at | updated_at
1  | admin | web | ... | ...
```

**permissions** table - Defines available permissions
```sql
id | name | guard_name | created_at | updated_at
1  | create posts | web | ... | ...
```

**role_has_permissions** - Links roles to permissions
```sql
permission_id | role_id
1 | 1 (admin has create posts)
```

**model_has_roles** - Links users to roles
```sql
model_id | model_type | role_id
1 | App\\Models\\User | 1 (user 1 has admin role)
```

### Best Practices

1. **Use Seeder** - Initialize roles and permissions in seeder
2. **Clear Cache** - After permission changes: `php artisan permission:cache-reset`
3. **Policy over Middleware** - Use policies for model-specific authorization
4. **Descriptive Names** - `create_blog_posts` not just `create`
5. **Guard Names** - Specify guard if using multiple auth guards
6. **Testing** - Always test permission checks in feature tests
