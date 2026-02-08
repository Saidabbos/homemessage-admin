<script setup>
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

const activeTab = ref('uz');

const tabs = [
  { key: 'uz', label: "O'zbek", flag: '🇺🇿' },
  { key: 'ru', label: 'Русский', flag: '🇷🇺' },
  { key: 'en', label: 'English', flag: '🇬🇧' },
];

const form = useForm({
  slug: '',
  status: true,
  en: { name: '', description: '' },
  uz: { name: '', description: '' },
  ru: { name: '', description: '' },
  sort_order: 0,
});

const generateSlug = () => {
  if (!form.slug && form.uz.name) {
    form.slug = form.uz.name
      .toLowerCase()
      .trim()
      .replace(/[^\w\s-]/g, '')
      .replace(/\s+/g, '-')
      .replace(/-+/g, '-');
  }
};

const submit = () => {
  form.post(route('admin.pressure-levels.store'), {
    preserveScroll: true,
  });
};

const hasTranslationErrors = (locale) => {
  return form.errors[`${locale}.name`] || form.errors[`${locale}.description`];
};
</script>

<template>
  <div>
    <!-- Content Header -->
    <div class="mb-4">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-[#1f2d3d]">Yangi Bosim Darajasi</h1>
          <p class="text-sm text-[#6c757d] mt-1">Yangi bosim darajasini yaratish</p>
        </div>
        <nav class="mt-2 sm:mt-0">
          <ol class="flex items-center text-sm">
            <li><Link href="/admin/dashboard" class="text-[#007bff]">Bosh sahifa</Link></li>
            <li class="mx-2 text-[#6c757d]">/</li>
            <li><Link href="/admin/pressure-levels" class="text-[#007bff]">Bosim Darajasi</Link></li>
            <li class="mx-2 text-[#6c757d]">/</li>
            <li class="text-[#6c757d]">Yangi</li>
          </ol>
        </nav>
      </div>
    </div>

    <form @submit.prevent="submit">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-4">
          <!-- Translations Card with Tabs -->
          <div class="bg-white rounded shadow-sm">
            <div class="border-b border-gray-200">
              <ul class="flex flex-wrap -mb-px">
                <li v-for="tab in tabs" :key="tab.key" class="mr-1">
                  <button
                    type="button"
                    @click="activeTab = tab.key"
                    :class="[
                      'inline-flex items-center gap-2 px-4 py-3 text-sm font-medium border-b-2 transition',
                      activeTab === tab.key
                        ? 'border-[#007bff] text-[#007bff]'
                        : 'border-transparent text-[#6c757d] hover:text-[#1f2d3d] hover:border-gray-300'
                    ]"
                  >
                    <span>{{ tab.flag }}</span>
                    <span>{{ tab.label }}</span>
                    <span v-if="hasTranslationErrors(tab.key)" class="w-2 h-2 bg-[#dc3545] rounded-full"></span>
                  </button>
                </li>
              </ul>
            </div>

            <div class="p-4">
              <!-- Uzbek -->
              <div v-show="activeTab === 'uz'" class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
                    Nomi (O'zbek) <span class="text-[#dc3545]">*</span>
                  </label>
                  <input v-model="form.uz.name" type="text" @blur="generateSlug"
                    class="w-full px-3 py-2 border rounded text-sm focus:ring-2 focus:ring-[#007bff] focus:border-[#007bff] transition"
                    :class="form.errors['uz.name'] ? 'border-[#dc3545]' : 'border-gray-300'"
                    placeholder="Masalan: Yumshoq" />
                  <p v-if="form.errors['uz.name']" class="mt-1 text-sm text-[#dc3545]">{{ form.errors['uz.name'] }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-[#1f2d3d] mb-1">Tavsifi (O'zbek)</label>
                  <textarea v-model="form.uz.description" rows="4"
                    class="w-full px-3 py-2 border rounded text-sm focus:ring-2 focus:ring-[#007bff] focus:border-[#007bff] transition"
                    :class="form.errors['uz.description'] ? 'border-[#dc3545]' : 'border-gray-300'"
                    placeholder="Bosim darajasi haqida batafsil ma'lumot..."></textarea>
                </div>
              </div>

              <!-- Russian -->
              <div v-show="activeTab === 'ru'" class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
                    Название (Русский) <span class="text-[#dc3545]">*</span>
                  </label>
                  <input v-model="form.ru.name" type="text"
                    class="w-full px-3 py-2 border rounded text-sm focus:ring-2 focus:ring-[#007bff] focus:border-[#007bff] transition"
                    :class="form.errors['ru.name'] ? 'border-[#dc3545]' : 'border-gray-300'"
                    placeholder="Например: Мягкое давление" />
                  <p v-if="form.errors['ru.name']" class="mt-1 text-sm text-[#dc3545]">{{ form.errors['ru.name'] }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-[#1f2d3d] mb-1">Описание (Русский)</label>
                  <textarea v-model="form.ru.description" rows="4"
                    class="w-full px-3 py-2 border rounded text-sm focus:ring-2 focus:ring-[#007bff] focus:border-[#007bff] transition"
                    :class="form.errors['ru.description'] ? 'border-[#dc3545]' : 'border-gray-300'"
                    placeholder="Подробное описание давления..."></textarea>
                </div>
              </div>

              <!-- English -->
              <div v-show="activeTab === 'en'" class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
                    Name (English) <span class="text-[#dc3545]">*</span>
                  </label>
                  <input v-model="form.en.name" type="text"
                    class="w-full px-3 py-2 border rounded text-sm focus:ring-2 focus:ring-[#007bff] focus:border-[#007bff] transition"
                    :class="form.errors['en.name'] ? 'border-[#dc3545]' : 'border-gray-300'"
                    placeholder="e.g., Light Pressure" />
                  <p v-if="form.errors['en.name']" class="mt-1 text-sm text-[#dc3545]">{{ form.errors['en.name'] }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-[#1f2d3d] mb-1">Description (English)</label>
                  <textarea v-model="form.en.description" rows="4"
                    class="w-full px-3 py-2 border rounded text-sm focus:ring-2 focus:ring-[#007bff] focus:border-[#007bff] transition"
                    :class="form.errors['en.description'] ? 'border-[#dc3545]' : 'border-gray-300'"
                    placeholder="Detailed description of pressure..."></textarea>
                </div>
              </div>
            </div>
          </div>

          <!-- Basic Info Card -->
          <div class="bg-white rounded shadow-sm">
            <div class="px-4 py-3 border-b border-gray-200">
              <h3 class="font-semibold text-[#1f2d3d] flex items-center">
                <svg class="w-4 h-4 mr-2 text-[#17a2b8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Asosiy Ma'lumotlar
              </h3>
            </div>
            <div class="p-4 space-y-4">
              <div>
                <label class="block text-sm font-medium text-[#1f2d3d] mb-1">Slug (URL)</label>
                <input v-model="form.slug" type="text"
                  class="w-full px-3 py-2 border rounded text-sm focus:ring-2 focus:ring-[#007bff] focus:border-[#007bff] transition"
                  :class="form.errors.slug ? 'border-[#dc3545]' : 'border-gray-300'"
                  placeholder="light" />
                <p class="mt-1 text-xs text-[#6c757d]">Avtomatik yaratiladi</p>
                <p v-if="form.errors.slug" class="mt-1 text-sm text-[#dc3545]">{{ form.errors.slug }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-[#1f2d3d] mb-1">Tartib Raqami</label>
                <input v-model.number="form.sort_order" type="number" min="0"
                  class="w-full px-3 py-2 border rounded text-sm focus:ring-2 focus:ring-[#007bff] focus:border-[#007bff] transition"
                  :class="form.errors.sort_order ? 'border-[#dc3545]' : 'border-gray-300'" />
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-4">
          <!-- Publish Card -->
          <div class="bg-white rounded shadow-sm">
            <div class="px-4 py-3 border-b border-gray-200 bg-[#007bff] rounded-t">
              <h3 class="font-semibold text-white flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Nashr Qilish
              </h3>
            </div>
            <div class="p-4">
              <div class="flex items-center justify-between mb-4">
                <span class="text-sm text-[#1f2d3d]">Status</span>
                <label class="relative inline-flex items-center cursor-pointer">
                  <input v-model="form.status" type="checkbox" class="sr-only peer">
                  <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[#007bff] rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#28a745]"></div>
                  <span class="ml-2 text-sm font-medium" :class="form.status ? 'text-[#28a745]' : 'text-[#6c757d]'">
                    {{ form.status ? 'Faol' : 'Nofaol' }}
                  </span>
                </label>
              </div>
              <div class="flex gap-2">
                <button type="submit" :disabled="form.processing"
                  class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-[#28a745] text-white text-sm font-medium rounded hover:bg-[#218838] transition disabled:opacity-50">
                  <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <svg v-else class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                  </svg>
                  Saqlash
                </button>
                <Link href="/admin/pressure-levels"
                  class="px-4 py-2 bg-[#6c757d] text-white text-sm font-medium rounded hover:bg-[#5a6268] transition">
                  Bekor
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>
