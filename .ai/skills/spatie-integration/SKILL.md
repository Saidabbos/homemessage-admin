---
name: spatie-integration
description: Build features with Spatie permissions and translatable models
---

# Spatie Integration Skill

This skill is for working with spatie/laravel-permission and spatie/laravel-translatable packages in this project.

## When to use this skill

Use this skill when:
- Creating new translatable models
- Setting up role-based access control
- Implementing permission checks in controllers
- Adding authorization policies
- Managing multi-language content
- Creating new roles or permissions

## Key Features

### 1. Translatable Models

Create new translatable models with consistent structure:

```php
class Article extends Model
{
    use HasTranslations;

    public $translatable = ['title', 'content'];

    protected $fillable = ['slug', 'published', 'title', 'content'];
}
```

Migration structure:
- Main table: `id`, `slug` (unique), other non-translatable columns, timestamps
- Translation table: `id`, `{table}_id` (FK), `locale`, translatable columns, timestamps

### 2. Permission System

Always implement permission checks:

```php
// In routes
Route::middleware('permission:create posts')->post('/posts', [PostController::class, 'store']);

// In controllers
$this->authorize('create', Post::class);

// In blade
@can('edit posts')
    <button>Edit</button>
@endcan
```

### 3. Role Assignment

Use consistent role assignment patterns:

```php
// Single role
$user->assignRole('editor');

// Multiple roles
$user->syncRoles(['editor', 'moderator']);

// Direct permission
$user->givePermissionTo('create posts');
```

## Database Tables Reference

- `roles` - Available roles (admin, editor, writer)
- `permissions` - Available permissions (create/edit/delete actions)
- `model_has_roles` - Links users to roles
- `role_has_permissions` - Links roles to permissions
- `{table}_translations` - Translation tables for each translatable model

## Cache Management

After permission changes, always clear cache:

```bash
php artisan permission:cache-reset
```

## Testing Checklist

When implementing permission/translatable features:

- [ ] Create/update with all supported locales
- [ ] Test permission checks in all user roles
- [ ] Verify authorization in blade templates
- [ ] Test fallback locale behavior
- [ ] Clear permission cache
- [ ] Test database transactions
- [ ] Verify migration integrity

## Common Patterns

### Pattern 1: Permission-Protected Create

```php
public function store(Request $request)
{
    $this->authorize('create', Post::class);

    $post = Post::create($request->validated());
    return redirect()->route('posts.show', $post);
}
```

### Pattern 2: Translatable Content Creation

```php
$post = Post::create([
    'slug' => 'my-post',
    'en' => [
        'title' => 'English Title',
        'content' => 'English content...',
    ],
    'uz' => [
        'title' => 'Uz Title',
        'content' => 'Uz content...',
    ],
]);
```

### Pattern 3: Authorization Policy

```php
class PostPolicy
{
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create posts');
    }

    public function update(User $user, Post $post): bool
    {
        return $user->hasPermissionTo('edit posts');
    }
}
```

## Migration Naming

Use consistent naming:
- Model: `Post`, Table: `posts`, Translation: `post_translations`
- Model: `Category`, Table: `categories`, Translation: `category_translations`

## Important Notes

1. Always define $translatable property in models
2. Use getTranslation() with fallback
3. Implement authorization policies for complex logic
4. Clear permission cache after programmatic changes
5. Test in all configured locales
6. Use consistent permission naming: `{verb}_{resource}`
