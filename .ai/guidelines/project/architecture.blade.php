## Project Architecture & Structure

This Laravel 12.x application uses modern architecture patterns with Spatie packages integration for permissions, roles, and multi-language support.

### Core Packages
- **spatie/laravel-permission** (v6.24.0) - Role and permission management
- **spatie/laravel-translatable** (v6.12.0) - Multi-language model attributes
- **laravel/boost** (v2.0.6) - AI-assisted development

### Database Models

#### User (with Roles & Permissions)
```php
// User model has:
// - HasRoles trait from Spatie\Permission
// - Can assign roles: admin, editor, writer
// - Permissions: create posts, edit posts, delete posts, view posts, create/edit categories

$user->assignRole('editor');
$user->hasPermissionTo('create posts');
$user->can('delete posts');
```

#### Post (Translatable)
```php
// Posts table: id, slug, published, timestamps
// post_translations table: post_id, locale, title, content, description
// Translatable locales: en, uz, ru

$post = Post::create([
    'slug' => 'my-post',
    'en' => ['title' => 'Hello', 'content' => '...'],
    'uz' => ['title' => 'Salom', 'content' => '...'],
]);

echo $post->getTranslation('title', 'uz'); // Multi-language access
```

#### Category (Translatable)
```php
// categories table: id, slug, timestamps
// category_translations table: category_id, locale, name, description
// Same translatable setup as Post

$category->hasMany(Post::class);
```

### Directory Structure

```
.ai/
├── guidelines/           # AI context guidelines
│   └── project/
│       ├── architecture.blade.php
│       └── permissions.blade.php
└── skills/              # Task-specific knowledge
    ├── translatable-models/
    ├── permission-system/
    └── spatie-integration/

app/
├── Models/
│   ├── User.php         # HasRoles trait
│   ├── Post.php         # HasTranslations trait
│   └── Category.php     # HasTranslations trait
├── Http/
│   ├── Controllers/     # PostController example
│   └── Middleware/      # Role/permission middleware
└── Policies/            # Authorization policies

database/
├── migrations/          # All migrations
├── seeders/
│   ├── DatabaseSeeder.php
│   ├── RoleAndPermissionSeeder.php
│   └── UserFactory.php
└── factories/           # Model factories

resources/
└── views/
    ├── posts/           # CRUD views for posts
    └── layouts/         # Base layout templates
```

### Key Patterns

#### 1. Permission Checking
Always use middleware or policy for authorization:

```php
// In routes
Route::middleware('permission:create posts')->post('/posts', [PostController::class, 'store']);

// In controllers
$this->authorize('create', Post::class);

// In Blade
@can('edit posts')
    <button>Edit</button>
@endcan
```

#### 2. Translatable Models
Always use $translatable property and getTranslation():

```php
class Post extends Model
{
    use HasTranslations;
    public $translatable = ['title', 'content', 'description'];
}

// Get with fallback
$post->getTranslation('title', 'uz') ?? $post->getTranslation('title', 'en');
```

#### 3. Role Assignment
Use seeders for base roles, then assign in controllers/commands:

```php
$user->assignRole('editor');
$user->syncRoles(['editor', 'contributor']);
$user->removeRole('writer');
```

### Test Users

```
admin@example.com     → admin role    → all permissions
editor@example.com    → editor role   → post & category management
writer@example.com    → writer role   → only post creation/editing
```

All factory users have password: `password`

### Important Notes

1. **Cache**: Clear permission cache after changes:
   ```bash
   php artisan permission:cache-reset
   ```

2. **Locales**: Set in `config/app.php`:
   ```php
   'locale' => 'en',
   'fallback_locale' => 'en',
   ```

3. **Translatable Columns**: Always define in model:
   ```php
   public $translatable = ['column1', 'column2'];
   ```

4. **Authorization**: Prefer policies over middleware for complex logic
