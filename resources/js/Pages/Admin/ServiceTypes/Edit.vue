<script setup>
import { ref } from 'vue';
import { useForm, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ImageUpload from '@/Components/Admin/ImageUpload.vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
  serviceType: Object,
});

const activeTab = ref('uz');

const tabs = [
  { key: 'uz', label: "O'zbek", flag: '🇺🇿' },
  { key: 'ru', label: 'Русский', flag: '🇷🇺' },
  { key: 'en', label: 'English', flag: '🇬🇧' },
];

const form = useForm({
  slug: props.serviceType.slug,
  duration: props.serviceType.duration,
  price: props.serviceType.price,
  image: null,
  status: props.serviceType.status,
  en: { name: props.serviceType.en?.name || '', description: props.serviceType.en?.description || '' },
  uz: { name: props.serviceType.uz?.name || '', description: props.serviceType.uz?.description || '' },
  ru: { name: props.serviceType.ru?.name || '', description: props.serviceType.ru?.description || '' },
});

const submit = () => {
  form.post(route('admin.service-types.update', props.serviceType.id), {
    preserveScroll: true,
  });
};

const deleteServiceType = () => {
  if (confirm('Haqiqatan ham bu massage turini o\'chirmoqchimisiz?')) {
    router.delete(route('admin.service-types.destroy', props.serviceType.id));
  }
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
          <h1 class="text-2xl font-semibold text-[#1f2d3d]">Tahrirlash</h1>
          <p class="text-sm text-[#6c757d] mt-1">{{ serviceType.uz?.name || serviceType.name }}</p>
        </div>
        <nav class="mt-2 sm:mt-0">
          <ol class="flex items-center text-sm">
            <li><Link href="/admin/dashboard" class="text-[#007bff]">Bosh sahifa</Link></li>
            <li class="mx-2 text-[#6c757d]">/</li>
            <li><Link href="/admin/service-types" class="text-[#007bff]">Massage Turlari</Link></li>
            <li class="mx-2 text-[#6c757d]">/</li>
            <li class="text-[#6c757d]">Tahrirlash</li>
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
              <!-- Tab Navigation -->
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
                    <span
                      v-if="hasTranslationErrors(tab.key)"
                      class="w-2 h-2 bg-[#dc3545] rounded-full"
                    ></span>
                  </button>
                </li>
              </ul>
            </div>

            <!-- Tab Content -->
            <div class="p-4">
              <!-- Uzbek -->
              <div v-show="activeTab === 'uz'" class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
                    Nomi (O'zbek) <span class="text-[#dc3545]">*</span>
                  </label>
                  <input
                    v-model="form.uz.name"
                    type="text"
                    class="w-full px-3 py-2 border rounded text-sm focus:ring-2 focus:ring-[#007bff] focus:border-[#007bff] transition"
                    :class="form.errors['uz.name'] ? 'border-[#dc3545]' : 'border-gray-300'"
                    placeholder="Masalan: Klassik massaj"
                  />
                  <p v-if="form.errors['uz.name']" class="mt-1 text-sm text-[#dc3545]">{{ form.errors['uz.name'] }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-[#1f2d3d] mb-1">Tavsifi (O'zbek)</label>
                  <textarea
                    v-model="form.uz.description"
                    rows="4"
                    class="w-full px-3 py-2 border rounded text-sm focus:ring-2 focus:ring-[#007bff] focus:border-[#007bff] transition"
                    :class="form.errors['uz.description'] ? 'border-[#dc3545]' : 'border-gray-300'"
                    placeholder="Xizmat haqida batafsil ma'lumot..."
                  ></textarea>
                  <p v-if="form.errors['uz.description']" class="mt-1 text-sm text-[#dc3545]">{{ form.errors['uz.description'] }}</p>
                </div>
              </div>

              <!-- Russian -->
              <div v-show="activeTab === 'ru'" class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
                    Название (Русский) <span class="text-[#dc3545]">*</span>
                  </label>
                  <input
                    v-model="form.ru.name"
                    type="text"
                    class="w-full px-3 py-2 border rounded text-sm focus:ring-2 focus:ring-[#007bff] focus:border-[#007bff] transition"
                    :class="form.errors['ru.name'] ? 'border-[#dc3545]' : 'border-gray-300'"
                    placeholder="Например: Классический массаж"
                  />
                  <p v-if="form.errors['ru.name']" class="mt-1 text-sm text-[#dc3545]">{{ form.errors['ru.name'] }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-[#1f2d3d] mb-1">Описание (Русский)</label>
                  <textarea
                    v-model="form.ru.description"
                    rows="4"
                    class="w-full px-3 py-2 border rounded text-sm focus:ring-2 focus:ring-[#007bff] focus:border-[#007bff] transition"
                    :class="form.errors['ru.description'] ? 'border-[#dc3545]' : 'border-gray-300'"
                    placeholder="Подробное описание услуги..."
                  ></textarea>
                  <p v-if="form.errors['ru.description']" class="mt-1 text-sm text-[#dc3545]">{{ form.errors['ru.description'] }}</p>
                </div>
              </div>

              <!-- English -->
              <div v-show="activeTab === 'en'" class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
                    Name (English) <span class="text-[#dc3545]">*</span>
                  </label>
                  <input
                    v-model="form.en.name"
                    type="text"
                    class="w-full px-3 py-2 border rounded text-sm focus:ring-2 focus:ring-[#007bff] focus:border-[#007bff] transition"
                    :class="form.errors['en.name'] ? 'border-[#dc3545]' : 'border-gray-300'"
                    placeholder="e.g., Classic Massage"
                  />
                  <p v-if="form.errors['en.name']" class="mt-1 text-sm text-[#dc3545]">{{ form.errors['en.name'] }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-[#1f2d3d] mb-1">Description (English)</label>
                  <textarea
                    v-model="form.en.description"
                    rows="4"
                    class="w-full px-3 py-2 border rounded text-sm focus:ring-2 focus:ring-[#007bff] focus:border-[#007bff] transition"
                    :class="form.errors['en.description'] ? 'border-[#dc3545]' : 'border-gray-300'"
                    placeholder="Detailed description of the service..."
                  ></textarea>
                  <p v-if="form.errors['en.description']" class="mt-1 text-sm text-[#dc3545]">{{ form.errors['en.description'] }}</p>
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
            <div class="p-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-[#1f2d3d] mb-1">Slug (URL)</label>
                  <input
                    v-model="form.slug"
                    type="text"
                    class="w-full px-3 py-2 border rounded text-sm focus:ring-2 focus:ring-[#007bff] focus:border-[#007bff] transition"
                    :class="form.errors.slug ? 'border-[#dc3545]' : 'border-gray-300'"
                    placeholder="klassik-massaj"
                  />
                  <p v-if="form.errors.slug" class="mt-1 text-sm text-[#dc3545]">{{ form.errors.slug }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-[#1f2d3d] mb-1">Davomiyligi (daqiqa)</label>
                  <input
                    v-model="form.duration"
                    type="number"
                    min="15"
                    max="480"
                    class="w-full px-3 py-2 border rounded text-sm focus:ring-2 focus:ring-[#007bff] focus:border-[#007bff] transition"
                    :class="form.errors.duration ? 'border-[#dc3545]' : 'border-gray-300'"
                  />
                  <p class="mt-1 text-xs text-[#6c757d]">Min: 15, Max: 480</p>
                  <p v-if="form.errors.duration" class="mt-1 text-sm text-[#dc3545]">{{ form.errors.duration }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-[#1f2d3d] mb-1">Narxi (so'm)</label>
                  <input
                    v-model="form.price"
                    type="number"
                    min="0"
                    step="1000"
                    class="w-full px-3 py-2 border rounded text-sm focus:ring-2 focus:ring-[#007bff] focus:border-[#007bff] transition"
                    :class="form.errors.price ? 'border-[#dc3545]' : 'border-gray-300'"
                  />
                  <p v-if="form.errors.price" class="mt-1 text-sm text-[#dc3545]">{{ form.errors.price }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-4">
          <!-- Publish Card -->
          <div class="bg-white rounded shadow-sm">
            <div class="px-4 py-3 border-b border-gray-200 bg-[#ffc107] rounded-t">
              <h3 class="font-semibold text-[#1f2d3d] flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Yangilash
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
              <div class="space-y-2">
                <button
                  type="submit"
                  :disabled="form.processing"
                  class="w-full inline-flex items-center justify-center px-4 py-2 bg-[#ffc107] text-[#1f2d3d] text-sm font-medium rounded hover:bg-[#e0a800] transition disabled:opacity-50"
                >
                  <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <svg v-else class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                  </svg>
                  Yangilash
                </button>
                <Link
                  href="/admin/service-types"
                  class="w-full inline-flex items-center justify-center px-4 py-2 bg-[#6c757d] text-white text-sm font-medium rounded hover:bg-[#5a6268] transition"
                >
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                  </svg>
                  Orqaga
                </Link>
                <button
                  type="button"
                  @click="deleteServiceType"
                  class="w-full inline-flex items-center justify-center px-4 py-2 bg-[#dc3545] text-white text-sm font-medium rounded hover:bg-[#c82333] transition"
                >
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                  </svg>
                  O'chirish
                </button>
              </div>
            </div>
          </div>

          <!-- Image Card -->
          <div class="bg-white rounded shadow-sm">
            <div class="px-4 py-3 border-b border-gray-200">
              <h3 class="font-semibold text-[#1f2d3d] flex items-center">
                <svg class="w-4 h-4 mr-2 text-[#17a2b8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Rasm
              </h3>
            </div>
            <div class="p-4">
              <!-- Current Image -->
              <div v-if="serviceType.image_url" class="mb-4">
                <p class="text-xs text-[#6c757d] mb-2">Joriy rasm:</p>
                <img :src="serviceType.image_url" :alt="serviceType.name" class="w-full rounded border border-gray-200" />
              </div>

              <ImageUpload
                v-model="form.image"
                :error="form.errors.image"
                :current-image="serviceType.image_url"
              />
              <p class="mt-2 text-xs text-[#6c757d]">Yangi rasm tanlash ixtiyoriy</p>
            </div>
          </div>

          <!-- Meta Info -->
          <div class="bg-white rounded shadow-sm">
            <div class="px-4 py-3 border-b border-gray-200">
              <h3 class="font-semibold text-[#1f2d3d] text-sm">Ma'lumot</h3>
            </div>
            <div class="p-4 text-sm text-[#6c757d] space-y-2">
              <div class="flex justify-between">
                <span>ID:</span>
                <span class="font-medium text-[#1f2d3d]">#{{ serviceType.id }}</span>
              </div>
              <div class="flex justify-between">
                <span>Yaratilgan:</span>
                <span>{{ new Date(serviceType.created_at).toLocaleDateString('uz-UZ') }}</span>
              </div>
              <div class="flex justify-between">
                <span>Yangilangan:</span>
                <span>{{ new Date(serviceType.updated_at).toLocaleDateString('uz-UZ') }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>
