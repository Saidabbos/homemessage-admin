<script setup>
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();

const props = defineProps({
  item: Object,
});

const activeTab = ref('uz');

const deleteItem = () => {
  if (confirm(t('standardItems.confirmDelete'))) {
    router.delete(route('admin.standard-items.destroy', props.item.id));
  }
};

const getTranslation = (key, locale) => {
  return props.item[locale]?.[key] || '-';
};
</script>

<template>
  <div>
    <!-- Content Header -->
    <div class="mb-4">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-[#1f2d3d]">{{ item.uz?.name || item.name }}</h1>
          <p class="text-sm text-[#6c757d] mt-1">{{ t('standardItems.info') }}</p>
        </div>
        <nav class="mt-2 sm:mt-0">
          <ol class="flex items-center text-sm">
            <li><Link href="/admin/dashboard" class="text-[#007bff]">{{ t('common.home') }}</Link></li>
            <li class="mx-2 text-[#6c757d]">/</li>
            <li><Link href="/admin/standard-items" class="text-[#007bff]">{{ t('standardItems.title') }}</Link></li>
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
              <span class="text-2xl mr-2">{{ item.icon || '📦' }}</span>
              {{ t('standardItems.info') }}
            </h3>
            <span
              :class="['inline-flex items-center px-2 py-1 text-xs font-medium rounded', item.status ? 'bg-[#d4edda] text-[#155724]' : 'bg-[#f8d7da] text-[#721c24]']"
            >
              {{ item.status ? t('common.active') : t('common.inactive') }}
            </span>
          </div>
          <div class="p-4">
            <div class="space-y-2 text-sm">
              <div class="flex items-center justify-between py-2 border-b border-gray-100">
                <span class="text-[#6c757d]">{{ t('common.slug') }}:</span>
                <code class="bg-[#f8f9fa] px-2 py-1 rounded text-[#1f2d3d]">{{ item.slug }}</code>
              </div>
              <div class="flex items-center justify-between py-2 border-b border-gray-100">
                <span class="text-[#6c757d]">{{ t('standardItems.sortOrder') }}:</span>
                <span class="font-medium text-[#1f2d3d]">{{ item.sort_order }}</span>
              </div>
              <div class="flex items-center justify-between py-2">
                <span class="text-[#6c757d]">{{ t('common.id') }}:</span>
                <span class="font-medium text-[#1f2d3d]">#{{ item.id }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Translations Card -->
        <div class="bg-white rounded shadow-sm">
          <div class="border-b border-gray-200">
            <ul class="flex flex-wrap -mb-px">
              <li v-for="tab in [{ key: 'uz', label: 'O\'zbek' }, { key: 'ru', label: 'Русский' }, { key: 'en', label: 'English' }]" :key="tab.key">
                <button
                  type="button"
                  @click="activeTab = tab.key"
                  :class="['inline-flex items-center gap-2 px-4 py-3 text-sm font-medium border-b-2 transition', activeTab === tab.key ? 'border-[#007bff] text-[#007bff]' : 'border-transparent text-[#6c757d] hover:text-[#1f2d3d]']"
                >
                  {{ tab.label }}
                </button>
              </li>
            </ul>
          </div>
          <div class="p-4">
            <div v-for="lang in ['uz', 'ru', 'en']" :key="lang" v-show="activeTab === lang" class="space-y-4">
              <div>
                <label class="block text-xs font-medium text-[#6c757d] uppercase tracking-wide mb-1">{{ t('translations.name') }}</label>
                <p class="text-lg font-semibold text-[#1f2d3d]">{{ getTranslation('name', lang) }}</p>
              </div>
              <div v-if="getTranslation('description', lang) !== '-'">
                <label class="block text-xs font-medium text-[#6c757d] uppercase tracking-wide mb-1">{{ t('translations.description') }}</label>
                <p class="text-[#1f2d3d] leading-relaxed">{{ getTranslation('description', lang) }}</p>
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
            <h3 class="font-semibold text-white">{{ t('common.actions') }}</h3>
          </div>
          <div class="p-4 space-y-2">
            <Link
              :href="route('admin.standard-items.edit', item.id)"
              class="w-full inline-flex items-center justify-center px-4 py-2 bg-[#ffc107] text-[#1f2d3d] text-sm font-medium rounded hover:bg-[#e0a800] transition"
            >
              {{ t('common.edit') }}
            </Link>
            <Link
              href="/admin/standard-items"
              class="w-full inline-flex items-center justify-center px-4 py-2 bg-[#6c757d] text-white text-sm font-medium rounded hover:bg-[#5a6268] transition"
            >
              {{ t('common.back') }}
            </Link>
            <button
              @click="deleteItem"
              class="w-full inline-flex items-center justify-center px-4 py-2 bg-[#dc3545] text-white text-sm font-medium rounded hover:bg-[#c82333] transition"
            >
              {{ t('common.delete') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
