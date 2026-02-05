<script setup>
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();

const props = defineProps({
  oil: Object,
});

const activeTab = ref('uz');

const tabs = [
  { key: 'uz', label: 'O\'zbek', flag: '🇺🇿' },
  { key: 'ru', label: 'Русский', flag: '🇷🇺' },
  { key: 'en', label: 'English', flag: '🇬🇧' },
];

const deleteOil = () => {
  if (confirm(t('oils.confirmDelete'))) {
    router.delete(route('admin.oils.destroy', props.oil.id));
  }
};

const getTranslation = (key, locale) => {
  return props.oil[locale]?.[key] || '-';
};
</script>

<template>
  <div>
    <!-- Content Header -->
    <div class="mb-4">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-[#1f2d3d]">{{ oil.uz?.name || oil.name }}</h1>
          <p class="text-sm text-[#6c757d] mt-1">{{ t('oils.info') }}</p>
        </div>
        <nav class="mt-2 sm:mt-0">
          <ol class="flex items-center text-sm">
            <li><Link href="/admin/dashboard" class="text-[#007bff]">{{ t('common.home') }}</Link></li>
            <li class="mx-2 text-[#6c757d]">/</li>
            <li><Link href="/admin/oils" class="text-[#007bff]">{{ t('oils.title') }}</Link></li>
            <li class="mx-2 text-[#6c757d]">/</li>
            <li class="text-[#6c757d]">{{ t('common.view') }}</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <!-- Main Content -->
      <div class="lg:col-span-2 space-y-4">
        <!-- Info Card -->
        <div class="bg-white rounded shadow-sm">
          <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
            <h3 class="font-semibold text-[#1f2d3d] flex items-center">
              <svg class="w-4 h-4 mr-2 text-[#17a2b8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              {{ t('oils.info') }}
            </h3>
            <span
              :class="[
                'inline-flex items-center px-2 py-1 text-xs font-medium rounded',
                oil.status ? 'bg-[#d4edda] text-[#155724]' : 'bg-[#f8d7da] text-[#721c24]'
              ]"
            >
              <span :class="['w-1.5 h-1.5 rounded-full mr-1.5', oil.status ? 'bg-[#28a745]' : 'bg-[#dc3545]']"></span>
              {{ oil.status ? t('common.active') : t('common.inactive') }}
            </span>
          </div>
          <div class="p-4">
            <div class="flex flex-col md:flex-row gap-6">
              <!-- Image -->
              <div class="md:w-48 flex-shrink-0">
                <img
                  :src="oil.image_url"
                  :alt="oil.name"
                  class="w-full rounded-lg border border-gray-200 object-cover"
                />
              </div>

              <!-- Details -->
              <div class="flex-1 space-y-4">
                <div class="bg-[#17a2b8] rounded p-4 text-white">
                  <p class="text-sm opacity-80">{{ t('oils.additionalPrice') }}</p>
                  <p class="text-2xl font-bold">+{{ oil.price?.toLocaleString() }}</p>
                  <p class="text-sm opacity-80">so'm</p>
                </div>

                <div class="space-y-2 text-sm">
                  <div class="flex items-center justify-between py-2 border-b border-gray-100">
                    <span class="text-[#6c757d]">{{ t('common.slug') }}:</span>
                    <code class="bg-[#f8f9fa] px-2 py-1 rounded text-[#1f2d3d]">{{ oil.slug }}</code>
                  </div>
                  <div class="flex items-center justify-between py-2 border-b border-gray-100">
                    <span class="text-[#6c757d]">{{ t('common.id') }}:</span>
                    <span class="font-medium text-[#1f2d3d]">#{{ oil.id }}</span>
                  </div>
                  <div class="flex items-center justify-between py-2 border-b border-gray-100">
                    <span class="text-[#6c757d]">{{ t('common.createdAt') }}:</span>
                    <span class="text-[#1f2d3d]">{{ new Date(oil.created_at).toLocaleString('uz-UZ') }}</span>
                  </div>
                  <div class="flex items-center justify-between py-2">
                    <span class="text-[#6c757d]">{{ t('common.updatedAt') }}:</span>
                    <span class="text-[#1f2d3d]">{{ new Date(oil.updated_at).toLocaleString('uz-UZ') }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

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
                </button>
              </li>
            </ul>
          </div>

          <!-- Tab Content -->
          <div class="p-4">
            <!-- Uzbek -->
            <div v-show="activeTab === 'uz'" class="space-y-4">
              <div>
                <label class="block text-xs font-medium text-[#6c757d] uppercase tracking-wide mb-1">{{ t('translations.name') }}</label>
                <p class="text-lg font-semibold text-[#1f2d3d]">{{ getTranslation('name', 'uz') }}</p>
              </div>
              <div v-if="getTranslation('description', 'uz') !== '-'">
                <label class="block text-xs font-medium text-[#6c757d] uppercase tracking-wide mb-1">{{ t('translations.description') }}</label>
                <p class="text-[#1f2d3d] leading-relaxed">{{ getTranslation('description', 'uz') }}</p>
              </div>
              <div v-else class="text-sm text-[#6c757d] italic">{{ t('translations.noDescription') }}</div>
            </div>

            <!-- Russian -->
            <div v-show="activeTab === 'ru'" class="space-y-4">
              <div>
                <label class="block text-xs font-medium text-[#6c757d] uppercase tracking-wide mb-1">{{ t('translations.name') }}</label>
                <p class="text-lg font-semibold text-[#1f2d3d]">{{ getTranslation('name', 'ru') }}</p>
              </div>
              <div v-if="getTranslation('description', 'ru') !== '-'">
                <label class="block text-xs font-medium text-[#6c757d] uppercase tracking-wide mb-1">{{ t('translations.description') }}</label>
                <p class="text-[#1f2d3d] leading-relaxed">{{ getTranslation('description', 'ru') }}</p>
              </div>
              <div v-else class="text-sm text-[#6c757d] italic">{{ t('translations.noDescription') }}</div>
            </div>

            <!-- English -->
            <div v-show="activeTab === 'en'" class="space-y-4">
              <div>
                <label class="block text-xs font-medium text-[#6c757d] uppercase tracking-wide mb-1">{{ t('translations.name') }}</label>
                <p class="text-lg font-semibold text-[#1f2d3d]">{{ getTranslation('name', 'en') }}</p>
              </div>
              <div v-if="getTranslation('description', 'en') !== '-'">
                <label class="block text-xs font-medium text-[#6c757d] uppercase tracking-wide mb-1">{{ t('translations.description') }}</label>
                <p class="text-[#1f2d3d] leading-relaxed">{{ getTranslation('description', 'en') }}</p>
              </div>
              <div v-else class="text-sm text-[#6c757d] italic">{{ t('translations.noDescription') }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="space-y-4">
        <!-- Actions Card -->
        <div class="bg-white rounded shadow-sm">
          <div class="px-4 py-3 border-b border-gray-200 bg-[#17a2b8] rounded-t">
            <h3 class="font-semibold text-white flex items-center">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
              </svg>
              {{ t('common.actions') }}
            </h3>
          </div>
          <div class="p-4 space-y-2">
            <Link
              :href="route('admin.oils.edit', oil.id)"
              class="w-full inline-flex items-center justify-center px-4 py-2 bg-[#ffc107] text-[#1f2d3d] text-sm font-medium rounded hover:bg-[#e0a800] transition"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
              {{ t('common.edit') }}
            </Link>
            <Link
              href="/admin/oils"
              class="w-full inline-flex items-center justify-center px-4 py-2 bg-[#6c757d] text-white text-sm font-medium rounded hover:bg-[#5a6268] transition"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
              </svg>
              {{ t('common.back') }}
            </Link>
            <button
              @click="deleteOil"
              class="w-full inline-flex items-center justify-center px-4 py-2 bg-[#dc3545] text-white text-sm font-medium rounded hover:bg-[#c82333] transition"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
              </svg>
              {{ t('common.delete') }}
            </button>
          </div>
        </div>

        <!-- Quick Stats -->
        <div class="bg-white rounded shadow-sm">
          <div class="px-4 py-3 border-b border-gray-200">
            <h3 class="font-semibold text-[#1f2d3d] text-sm">{{ t('common.statistics') }}</h3>
          </div>
          <div class="p-4 space-y-3">
            <div class="flex items-center justify-between">
              <span class="text-sm text-[#6c757d]">{{ t('common.orders') }}:</span>
              <span class="text-sm font-semibold text-[#1f2d3d]">0</span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-sm text-[#6c757d]">{{ t('common.rating') }}:</span>
              <div class="flex items-center">
                <svg class="w-4 h-4 text-[#ffc107]" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                <span class="text-sm font-semibold text-[#1f2d3d] ml-1">-</span>
              </div>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-sm text-[#6c757d]">{{ t('common.popularity') }}:</span>
              <span class="text-sm font-semibold text-[#1f2d3d]">0%</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
