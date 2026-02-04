## Multi-Language (Translatable) System

This project uses **spatie/laravel-translatable** for managing multi-language content.

### Supported Languages

| Code | Language | Default |
|------|----------|---------|
| en | English | ✓ |
| uz | Uzbek | |
| ru | Russian | |

Change default in `config/app.php`:
```php
'locale' => 'en',
'fallback_locale' => 'en',
```

### Translatable Models

#### Post Model
```php
class Post extends Model
{
    use HasTranslations;

    public $translatable = ['title', 'content', 'description'];

    protected $fillable = ['slug', 'published', 'title', 'content', 'description'];
}
```

Non-translatable columns: `id`, `slug`, `published`, `created_at`, `updated_at`

#### Category Model
```php
class Category extends Model
{
    use HasTranslations;

    public $translatable = ['name', 'description'];

    protected $fillable = ['slug', 'name', 'description'];
}
```

### Database Structure

#### Posts (Main Table)
```sql
id | slug (unique) | published (bool) | created_at | updated_at
1  | my-first-post | true | ... | ...
```

#### Post_translations (Translations Table)
```sql
id | post_id (FK) | locale | title | content | description | created_at | updated_at
1  | 1 | en | Hello World | English content | Short desc | ... | ...
2  | 1 | uz | Salom Dunyo | O'zbek kontenti | Qisqa tavsif | ... | ...
3  | 1 | ru | Привет Мир | Русское содержание | Краткое описание | ... | ...
```

### Creating Translatable Records

#### Single Language (Default Locale)
```php
$post = Post::create([
    'slug' => 'my-post',
    'title' => 'Hello World',        // en (default)
    'content' => 'English content',
    'description' => 'Short desc',
    'published' => true,
]);

// Internally stores in en locale
```

#### Multiple Languages at Once
```php
$post = Post::create([
    'slug' => 'my-post',
    'published' => true,
    'en' => [
        'title' => 'Hello World',
        'content' => 'English content',
        'description' => 'English description',
    ],
    'uz' => [
        'title' => 'Salom Dunyo',
        'content' => 'O\'zbek kontenti',
        'description' => 'O\'zbek tavsifi',
    ],
    'ru' => [
        'title' => 'Привет мир',
        'content' => 'Русское содержание',
        'description' => 'Русское описание',
    ],
]);
```

### Retrieving Translations

#### Get in Current Locale
```php
$post = Post::find(1);
app()->setLocale('uz');

echo $post->title;  // Salom Dunyo (Uzbek title)
```

#### Get Specific Locale
```php
$post = Post::find(1);

echo $post->getTranslation('title', 'uz');  // Salom Dunyo
echo $post->getTranslation('title', 'en');  // Hello World
echo $post->getTranslation('title', 'ru');  // Привет мир
```

#### Get with Fallback
```php
$post = Post::find(1);

// Return Uzbek, fallback to English if not exists
$title = $post->getTranslation('title', 'uz') ??
         $post->getTranslation('title', 'en');
```

#### Get All Translations
```php
$post = Post::find(1);

$post->translations;  // [
//   'en' => ['title' => '...', 'content' => '...'],
//   'uz' => ['title' => '...', 'content' => '...'],
// ]
```

### Updating Translations

#### Update Single Language
```php
$post = Post::find(1);
$post->setTranslation('title', 'uz', 'Yangi Sarlavha')
     ->save();
```

#### Update Multiple Languages
```php
$post = Post::find(1);

$post->update([
    'en' => ['title' => 'Updated Title', 'content' => '...'],
    'uz' => ['title' => 'Yangilangan Sarlavha', 'content' => '...'],
]);
```

#### Update Current Locale
```php
app()->setLocale('uz');

$post = Post::find(1);
$post->update(['title' => 'Yangi Sarlavha']); // Updates uz locale
```

### Checking Translations

```php
$post = Post::find(1);

// Check if translation exists
$post->hasTranslation('title', 'uz');     // true/false

// Get all locales with translations
$locales = array_keys($post->translations); // ['en', 'uz', 'ru']

// Check if locale exists
in_array('uz', $locales);
```

### Deleting Translations

```php
$post = Post::find(1);

// Delete specific locale translation
$post->forgetTranslation('title', 'uz');
$post->save();

// Delete entire record (all translations)
$post->delete();
```

### In Controllers

#### Store with Multiple Languages
```php
public function store(Request $request)
{
    $validated = $request->validate([
        'slug' => 'required|unique:posts',
        'en.title' => 'required',
        'en.content' => 'required',
        'uz.title' => 'required',
        'uz.content' => 'required',
    ]);

    $post = Post::create($validated);
    return redirect()->route('posts.show', $post);
}
```

#### Update with Multiple Languages
```php
public function update(Request $request, Post $post)
{
    $validated = $request->validate([
        'slug' => 'required|unique:posts,slug,' . $post->id,
        'en.title' => 'required',
        'en.content' => 'required',
        'uz.title' => 'required',
        'uz.content' => 'required',
    ]);

    $post->update($validated);
    return redirect()->route('posts.show', $post);
}
```

#### Show in Current Locale
```php
public function show(Post $post)
{
    $locale = app()->getLocale();

    return view('posts.show', [
        'post' => $post,
        'title' => $post->getTranslation('title', $locale),
        'content' => $post->getTranslation('content', $locale),
        'locale' => $locale,
    ]);
}
```

### In Blade Templates

#### Show Language Switcher
```blade
@php
    $locales = ['en', 'uz', 'ru'];
    $current = app()->getLocale();
@endphp

<div class="locale-selector">
    @foreach ($locales as $locale)
        <a href="?locale={{ $locale }}"
           class="{{ $current === $locale ? 'active' : '' }}">
            {{ strtoupper($locale) }}
        </a>
    @endforeach
</div>
```

#### Show Content in Current Locale
```blade
<h1>{{ $post->getTranslation('title', app()->getLocale()) }}</h1>
<p>{{ $post->getTranslation('content', app()->getLocale()) }}</p>
```

#### Show with Fallback
```blade
<h1>
    {{ $post->getTranslation('title', app()->getLocale()) ??
       $post->getTranslation('title', 'en') }}
</h1>
```

### Query Scopes

@verbatim
<code-snippet name="Filtering by Translatable Columns" lang="php">
// This requires custom implementation
// Get posts with title like something
$posts = Post::where('locale', 'en')
    ->join('post_translations', 'posts.id', '=', 'post_translations.post_id')
    ->where('post_translations.title', 'like', '%search%')
    ->get();
</code-snippet>
@endverbatim

### Best Practices

1. **Define $translatable array** - Always specify which columns are translatable
2. **Use array in create()** - Pass ['en' => [...], 'uz' => [...]] for multiple languages
3. **Set default locale** - Ensure config/app.php has correct default
4. **Fallback locale** - Set fallback for missing translations
5. **Test all locales** - Test content in all supported languages
6. **SEO slugs** - Keep slugs in single language (non-translatable)
7. **Cache translations** - Consider caching frequently accessed translations
8. **Migration naming** - Use `{table}_translations` for translation tables

### Common Issues & Solutions

#### Translation Not Showing
```php
// Problem: Forgot to set locale
app()->setLocale('uz');
$post->title; // Now shows Uzbek

// Problem: Translation doesn't exist
$post->getTranslation('title', 'uz') ?? $post->getTranslation('title', 'en');
```

#### Can't Create in Multiple Languages
```php
// Wrong
Post::create(['en.title' => 'Test']); // Doesn't work

// Right
Post::create([
    'en' => ['title' => 'Test'],
]);
```

#### Mass Assignment Not Working
```php
// Ensure fillable includes translatable columns
protected $fillable = ['slug', 'published', 'title', 'content', 'description'];
```
