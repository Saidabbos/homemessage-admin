---
name: permission-system
description: Implement role-based access control and authorization
---

# Permission System Skill

This skill covers implementing and managing the role-based access control system using spatie/laravel-permission.

## When to use this skill

Use this skill when:
- Creating new roles
- Adding new permissions
- Implementing authorization in controllers
- Creating authorization policies
- Setting up middleware for route protection
- Checking permissions in blade templates
- Managing user roles and permissions

## Current Permission Structure

### Existing Roles
- **admin** - Full system access (all permissions)
- **editor** - Content management (create, edit, delete posts; manage categories)
- **writer** - Content creation (create, edit posts only)

### Existing Permissions
- `create posts` - Create new posts
- `edit posts` - Edit existing posts
- `delete posts` - Delete posts
- `view posts` - View posts
- `create categories` - Create categories
- `edit categories` - Edit categories
- `delete categories` - Delete categories

## Adding New Roles

### Method 1: Via Seeder

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AddNewRoleSeeder extends Seeder
{
    public function run(): void
    {
        $moderator = Role::create(['name' => 'moderator']);

        $moderator->givePermissionTo([
            'view posts',
            'delete posts',
        ]);
    }
}
```

Run with:
```bash
php artisan db:seed --class=AddNewRoleSeeder
```

### Method 2: Via Tinker

```bash
php artisan tinker

>>> use Spatie\Permission\Models\Role;
>>> use Spatie\Permission\Models\Permission;

>>> $role = Role::create(['name' => 'moderator']);
>>> $role->givePermissionTo(['view posts', 'delete posts']);
```

## Adding New Permissions

### Method 1: Via Seeder

```php
Permission::create(['name' => 'publish posts']);
Permission::create(['name' => 'manage users']);
Permission::create(['name' => 'moderate comments']);
```

### Method 2: Via Tinker

```php
>>> Permission::create(['name' => 'publish posts']);
>>> Permission::create(['name' => 'manage users']);
```

## Assigning Roles to Users

### Single Role

```php
$user = User::find(1);
$user->assignRole('editor');
```

### Multiple Roles

```php
$user->assignRole(['editor', 'moderator']);
```

### Sync Roles (Replace All)

```php
// User will only have these roles
$user->syncRoles(['editor']);
```

### Remove Role

```php
$user->removeRole('editor');
```

### Direct Permission

```php
// Give permission directly to user (without role)
$user->givePermissionTo('edit posts');

// Revoke direct permission
$user->revokePermissionTo('delete posts');
```

## Authorization in Controllers

### Method 1: Using authorize() Helper

```php
public function edit(Post $post)
{
    // Check if user has permission
    $this->authorize('edit', $post);

    return view('posts.edit', compact('post'));
}

public function destroy(Post $post)
{
    // Can also check specific permission
    if (!auth()->user()->can('delete posts')) {
        abort(403, 'Not authorized');
    }

    $post->delete();
    return redirect()->route('posts.index');
}
```

### Method 2: Using Gates

```php
// In AuthServiceProvider boot()
Gate::define('edit-posts', function (User $user) {
    return $user->can('edit posts');
});

// In controller
if (Gate::denies('edit-posts')) {
    abort(403);
}
```

### Method 3: Using Policies

Create policy:
```bash
php artisan make:policy PostPolicy --model=Post
```

```php
<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

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
```

Register in `app/Providers/AuthServiceProvider.php`:

```php
protected $policies = [
    Post::class => PostPolicy::class,
];
```

Use in controller:
```php
public function update(Request $request, Post $post)
{
    $this->authorize('update', $post);

    $post->update($request->validated());
    return redirect()->route('posts.show', $post);
}
```

## Route Protection

### Middleware: Single Role

```php
Route::middleware('role:admin')->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
});
```

### Middleware: Multiple Roles (OR)

```php
Route::middleware('role:admin|editor')->group(function () {
    Route::resource('posts', PostController::class);
});
```

### Middleware: Single Permission

```php
Route::middleware('permission:create posts')->group(function () {
    Route::get('/posts/create', [PostController::class, 'create']);
    Route::post('/posts', [PostController::class, 'store']);
});
```

### Middleware: Multiple Permissions (AND)

```php
Route::middleware('permission:create posts,edit posts')->group(function () {
    Route::post('/posts', [PostController::class, 'store']);
});
```

### Combined

```php
Route::middleware('auth', 'role:editor', 'permission:delete posts')->group(function () {
    Route::delete('/posts/{post}', [PostController::class, 'destroy']);
});
```

## Blade Template Authorization

### Check Role

```blade
@role('admin')
    <p>You are an admin</p>
@endrole

@hasrole('editor|admin')
    <p>You are editor or admin</p>
@endhasrole
```

### Check Permission

```blade
@can('create posts')
    <a href="/posts/create" class="btn btn-primary">Create Post</a>
@endcan

@canany(['edit posts', 'delete posts'])
    <p>You can edit or delete posts</p>
@endcanany

@cannot('delete posts')
    <p>You cannot delete posts</p>
@endcannot
```

## Checking Permissions in Code

### Instance Methods

```php
$user = auth()->user();

// Role checks
$user->hasRole('admin');                    // true/false
$user->hasAnyRole(['admin', 'editor']);     // true/false
$user->hasAllRoles(['admin', 'editor']);    // true/false

// Permission checks
$user->hasPermissionTo('create posts');     // true/false
$user->can('edit posts');                   // true/false

// Get roles and permissions
$user->getRoleNames();          // Collection: ['admin']
$user->getAllPermissions();     // Collection of all permissions
```

### Static Methods

```php
// Get all roles
$roles = Role::all();

// Get all permissions
$permissions = Permission::all();

// Get specific role
$role = Role::findByName('admin');

// Get specific permission
$permission = Permission::findByName('create posts');
```

## Database Tables

### roles
```sql
id | name | guard_name | created_at | updated_at
```

### permissions
```sql
id | name | guard_name | created_at | updated_at
```

### role_has_permissions
```sql
permission_id | role_id
```

### model_has_roles
```sql
model_id | model_type | role_id
```

### model_has_permissions (Optional)
```sql
permission_id | model_id | model_type
```

## Important Operations

### Clear Permission Cache

After programmatic changes to permissions/roles:

```bash
php artisan permission:cache-reset
```

Or programmatically:
```php
app()['cache']->forget('spatie.permission.cache');
```

### Refresh User Permissions

If user permissions seem stale:

```php
auth()->user()->refreshPermissions();
```

### Get All User Permissions

```php
$permissions = auth()->user()->getAllPermissions();
// Returns collection of all permissions (direct + via roles)
```

## Best Practices

1. **Use Policies for Model Authorization**
   - Better for complex logic
   - Cleaner than gates and middleware

2. **Use Middleware for Route Protection**
   - Quick permission checks at route level
   - Prevents unnecessary controller execution

3. **Cache Permissions**
   - Spatie caches permissions automatically
   - Clear cache when making changes

4. **Consistent Permission Naming**
   - Use pattern: `{verb}_{resource}`
   - Example: `create_posts`, `edit_posts`, `delete_posts`

5. **Test Authorization**
   - Test each role/permission combination
   - Test policy logic thoroughly

6. **Use Direct Permissions Sparingly**
   - Prefer role-based assignment
   - Direct permissions harder to manage

## Troubleshooting

| Issue | Solution |
|-------|----------|
| Permission not working | Clear cache: `php artisan permission:cache-reset` |
| Middleware not protecting | Check permission name matches exactly |
| User has role but denied | Cache issue or role not assigned |
| Policy not called | Register in AuthServiceProvider |
| hasRole returns false | Check user actually has role in DB |

## Testing

```php
// In tests
$user = User::factory()->create();
$user->assignRole('editor');

$this->assertTrue($user->hasRole('editor'));
$this->assertTrue($user->can('create posts'));
$this->assertFalse($user->can('delete users'));
```
