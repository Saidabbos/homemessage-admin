<script setup>
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ImageUpload from '@/Components/Admin/ImageUpload.vue';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();

const activeTab = ref('uz');

const tabs = [
  { key: 'uz', label: 'O\'zbek', flag: '🇺🇿' },
  { key: 'ru', label: 'Русский', flag: '🇷🇺' },
  { key: 'en', label: 'English', flag: '🇬🇧' },
];

const form = useForm({
  slug: '',
  price: '',
  image: null,
  status: true,
  uz: { name: '', description: '' },
  ru: { name: '', description: '' },
  en: { name: '', description: '' },
});

const submit = () => {
  form.post(route('admin.oils.store'));
};

const generateSlug = () => {
  if (form.uz.name) {
    form.slug = form.uz.name
      .toLowerCase()
      .replace(/[^a-z0-9\s-]/g, '')
      .replace(/\s+/g, '-')
      .replace(/-+/g, '-')
      .trim();
  }
};
</script>

<template>
  <div>
    <!-- Content Header -->
    <div class="mb-4">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-[#1f2d3d]">{{ t('oils.new') }}</h1>
          <p class="text-sm text-[#6c757d] mt-1">{{ t('common.create') }} {{ t('oils.singular').toLowerCase() }}</p>
        </div>
        <nav class="mt-2 sm:mt-0">
          <ol class="flex items-center text-sm">
            <li><Link href="/admin/dashboard" class="text-[#007bff]">{{ t('common.home') }}</Link></li>
            <li class="mx-2 text-[#6c757d]">/</li>
            <li><Link href="/admin/oils" class="text-[#007bff]">{{ t('oils.title') }}</Link></li>
            <li class="mx-2 text-[#6c757d]">/</li>
            <li class="text-[#6c757d]">{{ t('common.create') }}</li>
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
            <div class="px-4 py-3 border-b border-gray-200 bg-[#17a2b8] rounded-t">
              <h3 class="font-semibold text-white flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                </svg>
                {{ t('translations.title') }}
              </h3>
            </div>

            <!-- Tab Navigation -->
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
                    {{ t('translations.name') }} <span class="text-[#dc3545]">*</span>
                  </label>
                  <input
                    type="text"
                    v-model="form.uz.name"
                    @blur="generateSlug"
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm"
                    :placeholder="t('oils.enterName')"
                  />
                  <div v-if="form.errors['uz.name']" class="text-[#dc3545] text-xs mt-1">{{ form.errors['uz.name'] }}</div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-[#1f2d3d] mb-1">{{ t('translations.description') }}</label>
                  <textarea
                    v-model="form.uz.description"
                    rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm"
                    :placeholder="t('oils.enterDescription')"
                  ></textarea>
                </div>
              </div>

              <!-- Russian -->
              <div v-show="activeTab === 'ru'" class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
                    {{ t('translations.name') }} <span class="text-[#dc3545]">*</span>
                  </label>
                  <input
                    type="text"
                    v-model="form.ru.name"
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm"
                    :placeholder="t('oils.enterName')"
                  />
                  <div v-if="form.errors['ru.name']" class="text-[#dc3545] text-xs mt-1">{{ form.errors['ru.name'] }}</div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-[#1f2d3d] mb-1">{{ t('translations.description') }}</label>
                  <textarea
                    v-model="form.ru.description"
                    rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm"
                    :placeholder="t('oils.enterDescription')"
                  ></textarea>
                </div>
              </div>

              <!-- English -->
              <div v-show="activeTab === 'en'" class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
                    {{ t('translations.name') }} <span class="text-[#dc3545]">*</span>
                  </label>
                  <input
                    type="text"
                    v-model="form.en.name"
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm"
                    :placeholder="t('oils.enterName')"
                  />
                  <div v-if="form.errors['en.name']" class="text-[#dc3545] text-xs mt-1">{{ form.errors['en.name'] }}</div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-[#1f2d3d] mb-1">{{ t('translations.description') }}</label>
                  <textarea
                    v-model="form.en.description"
                    rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm"
                    :placeholder="t('oils.enterDescription')"
                  ></textarea>
                </div>
              </div>
            </div>
          </div>

          <!-- Slug Card -->
          <div class="bg-white rounded shadow-sm">
            <div class="px-4 py-3 border-b border-gray-200">
              <h3 class="font-semibold text-[#1f2d3d] text-sm">{{ t('common.slug') }}</h3>
            </div>
            <div class="p-4">
              <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
                {{ t('common.slug') }} <span class="text-[#dc3545]">*</span>
              </label>
              <input
                type="text"
                v-model="form.slug"
                class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm font-mono"
                placeholder="oil-slug"
              />
              <p class="text-xs text-[#6c757d] mt-1">{{ t('validation.slugHint') }}</p>
              <div v-if="form.errors.slug" class="text-[#dc3545] text-xs mt-1">{{ form.errors.slug }}</div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-4">
          <!-- Image Upload Card -->
          <div class="bg-white rounded shadow-sm">
            <div class="px-4 py-3 border-b border-gray-200">
              <h3 class="font-semibold text-[#1f2d3d] text-sm">{{ t('common.image') }}</h3>
            </div>
            <div class="p-4">
              <ImageUpload v-model="form.image" />
              <div v-if="form.errors.image" class="text-[#dc3545] text-xs mt-2">{{ form.errors.image }}</div>
            </div>
          </div>

          <!-- Price Card -->
          <div class="bg-white rounded shadow-sm">
            <div class="px-4 py-3 border-b border-gray-200">
              <h3 class="font-semibold text-[#1f2d3d] text-sm">{{ t('common.price') }}</h3>
            </div>
            <div class="p-4">
              <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
                {{ t('oils.additionalPrice') }} (so'm) <span class="text-[#dc3545]">*</span>
              </label>
              <input
                type="number"
                v-model="form.price"
                min="0"
                step="1000"
                class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm"
                placeholder="0"
              />
              <p class="text-xs text-[#6c757d] mt-1">{{ t('oils.additionalPriceHint') }}</p>
              <div v-if="form.errors.price" class="text-[#dc3545] text-xs mt-1">{{ form.errors.price }}</div>
            </div>
          </div>

          <!-- Status Card -->
          <div class="bg-white rounded shadow-sm">
            <div class="px-4 py-3 border-b border-gray-200">
              <h3 class="font-semibold text-[#1f2d3d] text-sm">{{ t('common.status') }}</h3>
            </div>
            <div class="p-4">
              <label class="flex items-center cursor-pointer">
                <input
                  type="checkbox"
                  v-model="form.status"
                  class="w-4 h-4 text-[#007bff] border-gray-300 rounded focus:ring-[#007bff]"
                />
                <span class="ml-2 text-sm text-[#1f2d3d]">{{ t('common.active') }}</span>
              </label>
              <p class="text-xs text-[#6c757d] mt-2">{{ t('oils.activeHint') }}</p>
            </div>
          </div>

          <!-- Actions Card -->
          <div class="bg-white rounded shadow-sm">
            <div class="px-4 py-3 border-b border-gray-200 bg-[#28a745] rounded-t">
              <h3 class="font-semibold text-white text-sm">{{ t('common.actions') }}</h3>
            </div>
            <div class="p-4 space-y-2">
              <button
                type="submit"
                :disabled="form.processing"
                class="w-full inline-flex items-center justify-center px-4 py-2 bg-[#007bff] text-white text-sm font-medium rounded hover:bg-[#0069d9] transition disabled:opacity-50"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ form.processing ? t('common.saving') : t('common.save') }}
              </button>
              <Link
                href="/admin/oils"
                class="w-full inline-flex items-center justify-center px-4 py-2 bg-[#6c757d] text-white text-sm font-medium rounded hover:bg-[#5a6268] transition"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                {{ t('common.cancel') }}
              </Link>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>
