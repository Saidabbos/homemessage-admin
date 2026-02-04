# Developer Guide - Inertia Vue Admin Panel

## 📚 Architecture Overview

### Frontend Architecture
```
Request → Router → Controller → Inertia::render()
                       ↓
                    Data Props
                       ↓
                    Vue Page Component
                       ↓
                    HTML Response
```

### Key Technologies
- **Framework**: Laravel 12
- **Frontend**: Vue 3 (Composition API)
- **Build Tool**: Vite 7
- **UI Framework**: Tailwind CSS 4
- **State Management**: Vue Reactivity + Inertia
- **Authentication**: Laravel Session Guard + Spatie Permissions

---

## 🗂️ Directory Structure

### PHP - Backend
```
app/Http/
├── Controllers/Admin/
│   ├── AuthController.php
│   ├── DashboardController.php
│   └── ServiceTypeController.php
└── Middleware/
    ├── HandleInertiaRequests.php  ← Shares global data
    └── AdminMiddleware.php        ← Checks admin role

app/Models/
├── User.php
└── ServiceType.php
```

### Vue - Frontend
```
resources/js/
├── app.js                          ← Entry point
├── bootstrap.js                    ← Axios setup
├── Layouts/
│   ├── AdminLayout.vue            ← Admin pages wrapper
│   └── GuestLayout.vue            ← Login page wrapper
├── Pages/                          ← Full page components
│   └── Admin/
│       ├── Auth/Login.vue
│       ├── Dashboard.vue
│       └── ServiceTypes/
│           ├── Index.vue
│           ├── Create.vue
│           ├── Edit.vue
│           └── Show.vue
└── Components/                     ← Reusable components
    ├── Admin/
    │   ├── Sidebar.vue
    │   ├── Header.vue
    │   ├── FlashMessage.vue
    │   └── ImageUpload.vue
    ├── Form/
    │   ├── TextInput.vue
    │   ├── NumberInput.vue
    │   ├── TextArea.vue
    │   ├── Checkbox.vue
    │   └── Button.vue
    └── UI/
        ├── Pagination.vue
        └── Badge.vue
```

---

## 🔄 Data Flow Example

### Creating a Service Type

1. **User fills form** (`Pages/Admin/ServiceTypes/Create.vue`)
   ```vue
   const form = useForm({
     slug: '', duration: 60, price: 0, image: null,
     en: { name: '', description: '' },
     uz: { name: '', description: '' },
     ru: { name: '', description: '' },
   });
   ```

2. **Form submits to Laravel**
   ```vue
   form.post(route('admin.service-types.store'));
   ```

3. **Controller processes** (`app/Http/Controllers/Admin/ServiceTypeController.php`)
   ```php
   public function store(Request $request)
   {
       $validated = $request->validate([...]);
       if ($request->hasFile('image')) {
           $validated['image'] = $request->file('image')
               ->store('service-types', 'public');
       }
       ServiceType::create($validated);
       return redirect()->route('admin.service-types.index')
           ->with('success', 'Created successfully');
   }
   ```

4. **Inertia redirects to list** with flash message
   ```
   Redirect + Flash Message
          ↓
   HandleInertiaRequests middleware
          ↓
   Props shared globally (flash.success)
          ↓
   Index.vue receives props
          ↓
   FlashMessage component displays
   ```

---

## 🧩 Component Patterns

### Creating a New Form Component

```vue
<!-- Components/Form/CustomInput.vue -->
<script setup>
defineProps({
  modelValue: String,
  label: String,
  error: String,
  help: String,
});

defineEmits(['update:modelValue', 'blur']);
</script>

<template>
  <div>
    <label v-if="label" class="block text-sm font-medium text-gray-700 mb-2">
      {{ label }}
    </label>
    <input
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      @blur="$emit('blur')"
      :class="[
        'w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2',
        error
          ? 'border-red-300 focus:ring-red-500'
          : 'border-gray-300 focus:ring-purple-500'
      ]"
    />
    <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
    <p v-if="help" class="mt-1 text-xs text-gray-500">{{ help }}</p>
  </div>
</template>
```

### Using in a Page

```vue
<template>
  <CustomInput
    v-model="form.fieldName"
    label="Field Label"
    :error="form.errors.fieldName"
    help="Help text"
  />
</template>
```

---

## 🔗 Routing

### Frontend Routes (Vue Pages)

Routes are automatically generated from file structure:
```
Pages/Admin/Auth/Login.vue          → Admin/Auth/Login
Pages/Admin/Dashboard.vue           → Admin/Dashboard
Pages/Admin/ServiceTypes/Index.vue  → Admin/ServiceTypes/Index
Pages/Admin/ServiceTypes/Create.vue → Admin/ServiceTypes/Create
Pages/Admin/ServiceTypes/Edit.vue   → Admin/ServiceTypes/Edit
Pages/Admin/ServiceTypes/Show.vue   → Admin/ServiceTypes/Show
```

### Backend Routes (Laravel Routes)

```php
// routes/web.php
Route::prefix('admin')->middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::resource('service-types', ServiceTypeController::class, [
        'names' => [
            'index' => 'admin.service-types.index',
            'create' => 'admin.service-types.create',
            'store' => 'admin.service-types.store',
            'show' => 'admin.service-types.show',
            'edit' => 'admin.service-types.edit',
            'update' => 'admin.service-types.update',
            'destroy' => 'admin.service-types.destroy',
        ]
    ]);
});
```

### Using Routes in Vue

```vue
<!-- Using route() helper from Ziggy -->
<script setup>
// Helper is globally available
const loginUrl = route('admin.login');
const editUrl = route('admin.service-types.edit', item.id);
</script>

<template>
  <Link :href="route('admin.dashboard')">Dashboard</Link>
  <form :action="route('admin.service-types.store')" method="POST">
</template>
```

---

## 🎯 Adding New Pages

### Step 1: Create Controller Method

```php
// app/Http/Controllers/Admin/MyController.php
public function index()
{
    $data = Model::all();
    return Inertia::render('Admin/MyPage', [
        'items' => $data,
    ]);
}
```

### Step 2: Create Vue Page Component

```vue
<!-- resources/js/Pages/Admin/MyPage.vue -->
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

defineProps({
  items: Array,
});
</script>

<template>
  <div>
    <h1>My Page</h1>
    <!-- Content here -->
  </div>
</template>
```

### Step 3: Add Route

```php
// routes/web.php
Route::get('/my-page', [MyController::class, 'index'])->name('admin.my-page');
```

### Step 4: Navigation Link

```vue
<!-- In Sidebar or another component -->
<Link href="/admin/my-page">My Page</Link>
```

---

## 🎨 Styling Guide

### Tailwind Classes Used

**Colors:**
```
Purple: purple-500, purple-600, purple-700 (primary)
Green: green-100, green-500, green-800 (success)
Red: red-100, red-500, red-800 (danger)
Gray: gray-50 to gray-800 (neutrals)
```

**Common Classes:**
```
Padding: px-4, py-2, p-6
Gap: gap-4, gap-6
Grid: grid, grid-cols-2, grid-cols-1 md:grid-cols-3
Flexbox: flex, flex-col, items-center, justify-between
Borders: border, border-l-4, rounded-lg
Shadows: shadow-md, shadow-lg
Hover: hover:bg-purple-600, hover:text-gray-800
Transitions: transition, duration-200
```

### Custom Styles

```css
/* resources/css/app.css */
@import 'tailwindcss';

/* Add custom utilities if needed */
@utility button-primary {
  @apply bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg;
}
```

---

## 🔐 Authentication & Authorization

### User Data in Vue

Available globally via props:
```vue
<script setup>
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const user = page.props.auth.user;
// { id, name, email, avatar, role }
</script>
```

### Check Permissions in Controller

```php
// In controller before returning Inertia
if (!auth()->user()->can('edit-service-types')) {
    abort(403);
}
```

### Middleware Protection

```php
// Apply to routes
Route::middleware(['auth', 'admin'])->group(function () {
    Route::resource('items', ItemController::class);
});
```

---

## 🗄️ Working with Translations

### In Controller

```php
// Get specific translation
$name = $serviceType->getTranslation('name', 'uz');

// Set translations
$serviceType->setTranslations('name', [
    'en' => 'English name',
    'uz' => 'Uzbek name',
    'ru' => 'Russian name',
]);
```

### In Blade (Legacy)

```blade
{{ $serviceType->name }} <!-- Returns current locale -->
{{ $serviceType->getTranslation('name', 'uz') }} <!-- Specific locale -->
```

### In Vue

```vue
<!-- Structured data comes from controller -->
<script setup>
defineProps({
  serviceType: Object, // { uz: { name, description }, en: {...}, ru: {...} }
});

const getName = (locale) => serviceType[locale]?.name || '-';
</script>

<template>
  <div>
    <h2>{{ getName('uz') }}</h2>
    <p>{{ getName('en') }}</p>
  </div>
</template>
```

---

## 📝 Form Handling

### Using Inertia Form Helper

```vue
<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
  name: 'initial value',
  email: '',
});

const submit = () => {
  form.post(route('store'), {
    preserveScroll: true,  // Keep scroll position
    preserveState: false,  // Clear form after submit
    onSuccess: () => {
      console.log('Success!');
    },
  });
};
</script>

<template>
  <form @submit.prevent="submit">
    <input v-model="form.name" type="text" />
    <p v-if="form.errors.name">{{ form.errors.name }}</p>

    <button :disabled="form.processing" type="submit">
      {{ form.processing ? 'Saving...' : 'Save' }}
    </button>
  </form>
</template>
```

### Features

- **form.data()** - Get form data
- **form.reset()** - Reset to initial state
- **form.processing** - Is form submitting?
- **form.errors** - Validation errors from server
- **form.touched** - Which fields have been edited?
- **form.recentlySuccessful** - Did it just succeed?

---

## 🚨 Error Handling

### Flash Messages

```php
// In controller after action
return redirect()->route('admin.items.index')
    ->with('success', 'Item created successfully!');
    // or
    ->with('error', 'Something went wrong!');
```

### Display in Vue

```vue
<script setup>
import { usePage } from '@inertiajs/vue3';
import FlashMessage from '@/Components/Admin/FlashMessage.vue';

const page = usePage();
const flash = computed(() => page.props.flash);
</script>

<template>
  <FlashMessage
    :success="flash.success"
    :error="flash.error"
  />
</template>
```

### Validation Errors

```vue
<script setup>
const form = useForm({ name: '' });

const submit = () => {
  form.post(route('store'));
};
</script>

<template>
  <input v-model="form.name" type="text" />
  <p v-if="form.errors.name" class="text-red-600">
    {{ form.errors.name }}
  </p>
</template>
```

---

## 🔍 Debugging

### Vue DevTools

Install [Vue DevTools](https://devtools.vuejs.org/) browser extension to:
- Inspect Vue components
- Track reactive state
- Monitor component hierarchy

### Inertia DevTools

Inertia includes DevTools - press `Ctrl+Shift+I` (or Cmd+Shift+I on Mac) in the browser to see:
- Current page component
- Props being passed
- Request/response data

### Laravel Debugbar

Add to `.env`:
```
APP_DEBUG=true
```

Install debugbar:
```bash
composer require barryvdh/laravel-debugbar --dev
```

---

## 📦 Dependency Management

### Adding NPM Packages

```bash
# Install and add to package.json
npm install package-name

# Development dependency
npm install --save-dev package-name

# Rebuild if it's a build-time dependency
npm run build
```

### Adding Composer Packages

```bash
# Install via Docker
docker compose exec -T app composer require vendor/package

# Or locally if PHP is available
composer require vendor/package
```

---

## 🧪 Best Practices

### ✅ DO

- ✅ Use Vue components for reusable UI pieces
- ✅ Keep components small and focused
- ✅ Use TypeScript for complex logic (optional)
- ✅ Validate on both client and server
- ✅ Use meaningful component/variable names
- ✅ Comment complex logic
- ✅ Test with real data after deployment

### ❌ DON'T

- ❌ Manipulate DOM directly (use Vue)
- ❌ Store sensitive data in client-side state
- ❌ Skip server-side validation
- ❌ Use inline styles (use Tailwind)
- ❌ Create deeply nested components
- ❌ Modify props directly
- ❌ Make API calls directly (use controllers)

---

## 🚀 Performance Tips

1. **Code Splitting**: Vite automatically splits page components
2. **Lazy Loading**: Use dynamic imports for heavy components
3. **Image Optimization**: Use appropriate image sizes
4. **CSS**: Tailwind automatically removes unused classes in production
5. **Caching**: Set proper cache headers for static assets

---

## 📚 Useful Commands

```bash
# Development
npm run dev              # Start dev server
php artisan serve       # Start Laravel server

# Production
npm run build           # Build for production
php artisan cache:clear # Clear cache

# Database
php artisan migrate     # Run migrations
php artisan seed        # Seed database
php artisan tinker      # Interactive shell

# Maintenance
php artisan storage:link    # Link storage folder
php artisan config:clear    # Clear config cache
php artisan view:clear      # Clear compiled views
```

---

## 📖 Additional Resources

- [Vue 3 Guide](https://vuejs.org/guide/)
- [Inertia.js Documentation](https://inertiajs.com/)
- [Tailwind CSS Docs](https://tailwindcss.com/docs)
- [Laravel Documentation](https://laravel.com/docs/12.x)

---

**Last Updated:** February 5, 2026
**For Questions:** Check the main documentation files or review existing components for patterns.
