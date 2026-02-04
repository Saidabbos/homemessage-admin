# Spatie Packages Quick Reference

## 🔐 Permissions va Roles

### Tinker da Ishlash

```bash
docker compose exec app php artisan tinker
```

#### Permissions yaratish:
```php
use Spatie\Permission\Models\Permission;

Permission::create(['name' => 'edit articles']);
Permission::create(['name' => 'delete articles']);
```

#### Roles yaratish:
```php
use Spatie\Permission\Models\Role;

Role::create(['name' => 'moderator']);
Role::create(['name' => 'contributor']);
```

#### Role ga permission qo'shish:
```php
$role = Role::findByName('moderator');
$role->givePermissionTo('edit articles');
$role->givePermissionTo(['edit articles', 'delete articles']);
```

#### User ga role assign qilish:
```php
use App\Models\User;

$user = User::find(1);
$user->assignRole('moderator');
$user->assignRole(['moderator', 'contributor']);
```

#### Tekshirish:
```php
$user->hasRole('moderator');
$user->hasPermissionTo('edit articles');
$user->can('delete articles');
$user->getRoleNames(); // ['moderator', 'contributor']
```

#### Tekshirishlni o'chirish:
```php
$user->removeRole('contributor');
$user->revokePermissionTo('edit articles');
$user->syncRoles(['admin']); // Barcha role o'chirip admin qo'shish
```

---

## 🌍 Translatable Modellar

### Post Yaratish:

```php
use App\Models\Post;

// Simple - default locale (en)
$post = Post::create([
    'slug' => 'my-post',
    'title' => 'Hello World',
    'content' => 'Some content here',
]);

// Multiple locales
$post = Post::create([
    'slug' => 'my-post',
    'en' => [
        'title' => 'Hello World',
        'content' => 'English content',
    ],
    'uz' => [
        'title' => 'Salom Dunyo',
        'content' => 'O\'zbek kontenti',
    ],
    'ru' => [
        'title' => 'Привет мир',
        'content' => 'Русское содержание',
    ],
]);
```

### Translations olish:

```php
$post = Post::find(1);

// Default locale
echo $post->title; // Current app locale

// Specific locale
echo $post->getTranslation('title', 'uz');

// Barcha translations
$post->translations; // Hamma tillar

// Translation mavjudligini tekshirish
$post->hasTranslation('title', 'uz'); // true/false
```

### Locales o'zgartirish:

```php
// App config da locale o'zgartiring
app()->setLocale('uz');

// Keyin modelni oling
$post = Post::find(1);
echo $post->title; // Uz tilida chiqadi
```

### Update qilish:

```php
$post = Post::find(1);

// Current locale
$post->update(['title' => 'New Title']);

// Specific locale
$post->setTranslation('title', 'uz', 'Yangi Sarlavha')
     ->setTranslation('title', 'en', 'New Title')
     ->save();

// Yoki
$post->update([
    'uz' => ['title' => 'Yangi Sarlavha'],
    'en' => ['title' => 'New Title'],
]);
```

### Delete:

```php
$post = Post::find(1);

// Barchi deleteQa qaytaradi
$post->delete();

// Faqat ma'lum locale
$post->forgetTranslation('title', 'uz');
$post->save();
```

---

## 📊 Database Queries

### Users va Roles:

```bash
# Roles ro'yxati
docker compose exec mysql mysql -uroot -proot_password -h localhost laravel -e "SELECT * FROM roles;"

# Permissions ro'yxati
docker compose exec mysql mysql -uroot -proot_password -h localhost laravel -e "SELECT * FROM permissions;"

# User roles
docker compose exec mysql mysql -uroot -proot_password -h localhost laravel -e "SELECT * FROM model_has_roles;"

# Role permissions
docker compose exec mysql mysql -uroot -proot_password -h localhost laravel -e "SELECT * FROM role_has_permissions;"
```

### Posts va Translations:

```bash
# Posts
docker compose exec mysql mysql -uroot -proot_password -h localhost laravel -e "SELECT * FROM posts;"

# Post translations
docker compose exec mysql mysql -uroot -proot_password -h localhost laravel -e "SELECT * FROM post_translations;"
```

---

## 🛂 Routes va Middleware

### Roles Middleware:

```php
Route::group(['middleware' => 'role:admin'], function () {
    Route::get('/admin', [AdminController::class, 'dashboard']);
});

// Multiple roles (OR)
Route::group(['middleware' => 'role:admin|editor'], function () {
    Route::resource('posts', PostController::class);
});
```

### Permissions Middleware:

```php
Route::group(['middleware' => 'permission:create posts'], function () {
    Route::get('/posts/create', [PostController::class, 'create']);
});

// Multiple permissions (AND)
Route::group(['middleware' => 'permission:create posts|edit posts'], function () {
    Route::post('/posts', [PostController::class, 'store']);
});
```

### Combined:

```php
Route::group(['middleware' => 'auth'], function () {
    Route::group(['middleware' => 'role:editor'], function () {
        Route::group(['middleware' => 'permission:delete posts'], function () {
            Route::delete('/posts/{id}', [PostController::class, 'destroy']);
        });
    });
});
```

---

## 📝 Blade Directives

```blade
<!-- Role tekshirish -->
@role('admin')
    <p>Siz admin!</p>
@endrole

<!-- Multiple roles -->
@hasrole('editor|admin')
    <p>Siz editor yoki admin!</p>
@endhasrole

<!-- Permission tekshirish -->
@can('create posts')
    <a href="/posts/create">Post yaratish</a>
@endcan

<!-- Multiple permissions -->
@canany(['create posts', 'edit posts'])
    <p>Qayta tahrir yoki yaratishga ruxsatingiz bor</p>
@endcanany

<!-- Cannot -->
@cannot('delete posts')
    <p>Siz postni o'cha olmaysiz</p>
@endcannot
```

---

## 🧪 Test Qilish

```bash
# Tinker orqali tekshirish
docker compose exec app php artisan tinker

# Admin user bilang tekshirish
>>> Auth::login(User::find(1)) // Admin
>>> auth()->user()->hasRole('admin') // true
>>> auth()->user()->can('delete posts') // true

# Writer user bilan
>>> Auth::login(User::find(3)) // Writer
>>> auth()->user()->can('delete posts') // false
```

---

## 🔧 Cache Masalalar

Agar permission/role o'zgartirilganida qayta ishlama qo'ylsa:

```bash
docker compose exec app php artisan cache:clear
docker compose exec app php artisan permission:cache-reset
```

---

## 📚 Mashaqqatli Ishlash

### Permissions Statistics:

```bash
docker compose exec app php artisan tinker

>>> use Spatie\Permission\Models\Role;
>>> Role::with('permissions')->get();

>>> use Spatie\Permission\Models\Permission;
>>> Permission::with('roles')->get();

>>> use App\Models\User;
>>> User::with('roles', 'permissions')->get();
```

### Translatable Statistics:

```bash
>>> use App\Models\Post;
>>> $post = Post::find(1);
>>> $post->translations; // Hamma tillar

>>> Post::all()->map(fn($p) => $p->getTranslation('title', 'uz'));
```

---

## ⚡ Performance Tips

1. **Roles va Permissions**: Databaseda cache bo'ladi, o'zgartirilganidan keyin cache clear qiling
2. **Translatable**: Ko'p til qo'shilsa, query optimize qiling (select, with, etc.)
3. **Seeding**: Dastlab seeder bilan setup qiling, keyin tinker da o'zgartirib test qiling

---

## 🆘 Muammo va Yechim

### "Undefined method 'assignRole'"
```php
// User modelga trait qo'shing
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable {
    use HasRoles;
}
```

### "Locale translation not found"
```php
// config/app.php da fallback_locale sozlang
'fallback_locale' => 'en',

// Yoki explicit sozlang
$post->getTranslation('title', 'uz') ?? $post->getTranslation('title', 'en');
```

### Permission cache issues
```bash
docker compose exec app php artisan permission:cache-reset
docker compose exec app php artisan cache:clear
```
