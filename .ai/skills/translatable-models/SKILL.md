---
name: translatable-models
description: Create and manage multi-language translatable models
---

# Translatable Models Skill

This skill focuses on creating and managing translatable models using spatie/laravel-translatable.

## When to use this skill

Use this skill when:
- Creating new translatable models (Product, Article, Page, etc.)
- Building migrations for translatable tables
- Implementing multi-language features
- Creating seeders for translatable data
- Building forms for multi-language content

## Step-by-Step Model Creation

### 1. Create Model with Trait

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Article extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'slug',
        'published',
        'title',
        'content',
        'description',
    ];

    public $translatable = [
        'title',
        'content',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'published' => 'boolean',
        ];
    }
}
```

### 2. Create Migration

Migration file structure:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Main table (non-translatable columns)
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->boolean('published')->default(false);
            $table->timestamps();
        });

        // Translations table
        Schema::create('article_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained('articles')->onDelete('cascade');
            $table->string('locale')->index();
            $table->string('title');
            $table->longText('content');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->unique(['article_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_translations');
        Schema::dropIfExists('articles');
    }
};
```

### 3. Create Factory (Optional)

```php
<?php

namespace Database\Factories;

use App\Models\Article;
use Illuminate\Database\Eloquent\Factories\Factory;

class ArticleFactory extends Factory
{
    protected $model = Article::class;

    public function definition(): array
    {
        return [
            'slug' => $this->faker->unique()->slug(),
            'published' => $this->faker->boolean(),
            'en' => [
                'title' => $this->faker->sentence(),
                'content' => $this->faker->paragraphs(3, true),
                'description' => $this->faker->sentence(10),
            ],
            'uz' => [
                'title' => $this->faker->sentence(),
                'content' => $this->faker->paragraphs(3, true),
                'description' => $this->faker->sentence(10),
            ],
        ];
    }
}
```

### 4. Create Seeder (Optional)

```php
<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        Article::create([
            'slug' => 'welcome-article',
            'published' => true,
            'en' => [
                'title' => 'Welcome to Our Blog',
                'content' => 'This is the English content...',
                'description' => 'Welcome article description',
            ],
            'uz' => [
                'title' => 'Blogimizga Xush Kelibsiz',
                'content' => 'Bu o\'zbek kontenti...',
                'description' => 'Xush Kelibsiz maqoLA tavsifi',
            ],
            'ru' => [
                'title' => 'Добро пожаловать в наш блог',
                'content' => 'Это русское содержание...',
                'description' => 'Описание приветственной статьи',
            ],
        ]);

        Article::factory(10)->create();
    }
}
```

## Common Usage Patterns

### Pattern 1: Create in Multiple Languages

```php
$article = Article::create([
    'slug' => 'my-article',
    'published' => true,
    'en' => [
        'title' => 'My Article',
        'content' => 'Content in English...',
        'description' => 'Article description',
    ],
    'uz' => [
        'title' => 'Mening Maqolam',
        'content' => 'O\'zbek kontenti...',
        'description' => 'Maqola tavsifi',
    ],
]);
```

### Pattern 2: Update Translations

```php
$article = Article::find(1);

// Update specific locale
$article->setTranslation('title', 'uz', 'Yangi Sarlavha')->save();

// Or update multiple at once
$article->update([
    'en' => ['title' => 'Updated Title'],
    'uz' => ['title' => 'Yangilangan Sarlavha'],
]);
```

### Pattern 3: Query by Locale

```php
// Get with English titles
$articles = Article::all();
app()->setLocale('en');
// Access $article->title returns English

// Get with specific locale
app()->setLocale('uz');
$uzbekArticles = Article::all();
// Now $article->title returns Uzbek
```

### Pattern 4: Controller Implementation

```php
public function show(Article $article)
{
    $locale = app()->getLocale();

    return view('articles.show', [
        'article' => $article,
        'title' => $article->getTranslation('title', $locale),
        'content' => $article->getTranslation('content', $locale),
    ]);
}

public function store(Request $request)
{
    $validated = $request->validate([
        'slug' => 'required|unique:articles',
        'published' => 'boolean',
        'en.title' => 'required',
        'en.content' => 'required',
        'uz.title' => 'required',
        'uz.content' => 'required',
    ]);

    $article = Article::create($validated);

    return redirect()->route('articles.show', $article)
        ->with('success', 'Article created successfully');
}
```

## Best Practices

1. **Always use getTranslation() with fallback**
   ```php
   $title = $article->getTranslation('title', 'uz') ??
            $article->getTranslation('title', 'en');
   ```

2. **Define all translatable columns in $translatable**
   ```php
   public $translatable = ['title', 'content', 'description'];
   ```

3. **Keep slugs non-translatable**
   ```php
   // Slug is unique across all locales
   // Don't add 'slug' to $translatable
   ```

4. **Validate all locales in forms**
   ```php
   'en.title' => 'required',
   'uz.title' => 'required',
   'ru.title' => 'required',
   ```

5. **Cache translations if frequently accessed**
   ```php
   $title = cache()->remember("article.{$id}.title.{$locale}", now()->addDay(), function() {
       return $article->getTranslation('title', $locale);
   });
   ```

6. **Test in all configured locales**
   ```bash
   php artisan tinker
   >>> $article = Article::find(1)
   >>> app()->setLocale('en')
   >>> $article->title
   >>> app()->setLocale('uz')
   >>> $article->title
   ```

## Troubleshooting

| Issue | Solution |
|-------|----------|
| Translation not appearing | Check locale is set correctly, verify translation exists |
| Mass assignment error | Add translatable columns to $fillable |
| Query not filtering | Use joins with translation table |
| Cache issues | Clear with `php artisan cache:clear` |
| Unique constraint error | Check {table_id, locale} composite unique constraint |

## File Locations

- Models: `app/Models/Article.php`
- Migrations: `database/migrations/xxxx_xx_xx_create_articles_table.php`
- Factories: `database/factories/ArticleFactory.php`
- Seeders: `database/seeders/ArticleSeeder.php`
- Views: `resources/views/articles/`
- Controllers: `app/Http/Controllers/ArticleController.php`
