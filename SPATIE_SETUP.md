# Spatie Laravel Packages Setup Guide

Bu guide laravel-permissions va laravel-translatable packagelarning implementatsiyasini tushuntiradi.

## O'rnatilgan Packagelar

1. **spatie/laravel-permission** (v6.24.0) - Role va Permission boshqaruvi
2. **spatie/laravel-translatable** (v6.12.0) - Ko'p tillik model atributlari

## 1. Laravel Permissions

### Asosiy Tushunchalar

- **Permissions**: Foydalanuvchi qilishi mumkin bo'lgan harakatlar (create, edit, delete)
- **Roles**: Permissionlarning guruhlari
- **Users**: Roles orqali permissions olgan foydalanuvchilar

### User Modeli (Allaqachon sozlangan)

```php
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;
}
```

### Permissionlar va Rollar Yaratish

#### Tinker orqali (CLI):
```bash
docker compose exec app php artisan tinker
```

```php
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

// Permissions yaratish
Permission::create(['name' => 'create posts']);
Permission::create(['name' => 'edit posts']);
Permission::create(['name' => 'delete posts']);
Permission::create(['name' => 'view posts']);

// Roles yaratish
$editorRole = Role::create(['name' => 'editor']);
$writerRole = Role::create(['name' => 'writer']);

// Rolga permissions qo'shish
$editorRole->givePermissionTo(['create posts', 'edit posts', 'delete posts']);
$writerRole->givePermissionTo(['create posts', 'edit posts']);

// Foydalanuvchiga role assign qilish
$user = User::find(1);
$user->assignRole('editor');
```

### Permissionlar bilan Ishlash

```php
// Userda role bor-yo'qligini tekshirish
$user->hasRole('editor');
$user->hasAnyRole(['editor', 'admin']);

// Userde permission bor-yo'qligini tekshirish
$user->hasPermissionTo('create posts');
$user->can('edit posts');

// Barcha rollarni olish
$user->getRoleNames(); // Collection

// Barcha permissionlarni olish
$user->getAllPermissions(); // Collection

// Foydalanuvchiga role o'chirish
$user->removeRole('writer');
$user->syncRoles(['editor', 'admin']);
```

### Blade Templates da Kontrol

```blade
@role('admin')
    Admin paneli...
@endrole

@hasrole('editor|admin')
    Editor yoki Admin...
@endhasrole

@can('create posts')
    Post yaratish tugmasi
@endcan
```

### Middleware Bilan Zaxit Qilish

```php
Route::group(['middleware' => 'role:editor'], function () {
    Route::post('/posts', [PostController::class, 'store']);
});

Route::group(['middleware' => 'permission:create posts'], function () {
    Route::get('/posts/create', [PostController::class, 'create']);
});
```

## 2. Laravel Translatable

### Asosiy Tushunchalar

- **Translatable Attributes**: Model atributlari ko'p tillarda saqlash
- **Locales**: Tillar (en, uz, ru, etc.)
- **Translation Table**: Har bir model uchun translation jadvali

### Model Sozlash

```php
use Spatie\Translatable\HasTranslations;

class Post extends Model
{
    use HasTranslations;

    // Qaysi atributlar ko'p tillida saqlanadigan aytish
    public $translatable = ['title', 'content', 'description'];
}
```

### Translation Jadvali Struktura

```
posts table:
- id
- slug (не-translatable)
- published (не-translatable)
- created_at, updated_at

post_translations table:
- id
- post_id
- locale (en, uz, ru)
- title
- content
- description
- created_at, updated_at
```

### Post Yaratish va Yangilash

```php
// Post yaratish
$post = Post::create([
    'slug' => 'my-first-post',
    'published' => true,
    'title' => 'My First Post', // Default locale (en)
    'content' => 'This is content...',
    'description' => 'Short description',
]);

// Boshqa tilda yaratish
$post = Post::create([
    'slug' => 'my-first-post',
    'en' => [
        'title' => 'My First Post',
        'content' => 'English content...',
        'description' => 'English description',
    ],
    'uz' => [
        'title' => 'Mening Birinchi Post',
        'content' => 'O\'zbek tili kontenti...',
        'description' => 'O\'zbek tavsifi',
    ],
]);
```

### Translation Olish

```php
$post = Post::find(1);

// Default locale (app.locale config)
echo $post->title;

// Muayyan locale
echo $post->getTranslation('title', 'uz');

// Tilni o'zgartirish
$post->setLocale('uz');
echo $post->title;

// Barcha translations
$post->translations; // array

// Translation mavjudligini tekshirish
$post->hasTranslation('title', 'uz');
```

### Locales Sozlash

`config/app.php` da:
```php
'locale' => 'en', // Default locale
'fallback_locale' => 'en', // Agar translation bo'lmasa
```

### Category Model

```php
class Category extends Model
{
    use HasFactory, HasTranslations;

    public $translatable = ['name', 'description'];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
```

## Ishlatiladigan Misollar

### Role va Permission bilan Admin Panel

```php
// Route Protection
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionController::class);
});

// Controller da
public function store(Request $request)
{
    // Foydalanuvchiga permission bor-yo'qligini tekshirish
    if (!auth()->user()->can('create posts')) {
        abort(403, 'Sizga ruxsat yo\'q');
    }

    Post::create($request->all());
}
```

### Ko'p tillik Front-end

```blade
<!-- Tilni saqlash va ko'rsatish -->
@php
    $locales = ['en', 'uz', 'ru'];
    $currentLocale = app()->getLocale();
@endphp

<div class="locale-selector">
    @foreach ($locales as $locale)
        <a href="{{ url()->current() }}?locale={{ $locale }}"
           class="{{ $currentLocale === $locale ? 'active' : '' }}">
            {{ strtoupper($locale) }}
        </a>
    @endforeach
</div>

<!-- Post ko'rsatish -->
<h1>{{ $post->getTranslation('title', $currentLocale) }}</h1>
<p>{{ $post->getTranslation('description', $currentLocale) }}</p>
```

## Qo'shimcha Buyruqlar

### Permissions ro'yxati
```bash
docker compose exec app php artisan tinker
>>> Permission::all()
```

### Database Tekshirish
```bash
# Posts jadvali
docker compose exec mysql mysql -uroot -proot_password -h localhost laravel -e "DESC posts;"
docker compose exec mysql mysql -uroot -proot_password -h localhost laravel -e "DESC post_translations;"

# Permissions jadvali
docker compose exec mysql mysql -uroot -proot_password -h localhost laravel -e "DESC roles;"
docker compose exec mysql mysql -uroot -proot_password -h localhost laravel -e "DESC permissions;"
```

## Foydalanuvchi va Admin Seeding

`database/seeders/DatabaseSeeder.php` da:

```php
public function run(): void
{
    // Permissions yaratish
    Permission::create(['name' => 'create posts']);
    Permission::create(['name' => 'edit posts']);
    Permission::create(['name' => 'delete posts']);

    // Roles yaratish
    $admin = Role::create(['name' => 'admin']);
    $editor = Role::create(['name' => 'editor']);

    // Role permissions
    $admin->givePermissionTo(Permission::all());
    $editor->givePermissionTo(['create posts', 'edit posts']);

    // Users yaratish
    User::factory()->create([
        'name' => 'Admin User',
        'email' => 'admin@example.com',
    ])->assignRole('admin');

    User::factory()->create([
        'name' => 'Editor User',
        'email' => 'editor@example.com',
    ])->assignRole('editor');
}
```

Seeder ishga tushirish:
```bash
docker compose exec app php artisan db:seed
```

## Foydali Havolalar

- [Spatie Permissions Docs](https://spatie.be/docs/laravel-permission/v6/introduction)
- [Spatie Translatable Docs](https://spatie.be/docs/laravel-translatable/v6/introduction)
