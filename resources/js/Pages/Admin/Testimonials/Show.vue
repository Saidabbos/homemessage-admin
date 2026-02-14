<script setup>
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

const { t, locale } = useI18n();

const props = defineProps({
  testimonial: Object,
});

const activeTab = ref('uz');

const tabs = [
  { key: 'uz', label: 'O\'zbek', flag: '🇺🇿' },
  { key: 'ru', label: 'Русский', flag: '🇷🇺' },
  { key: 'en', label: 'English', flag: '🇬🇧' },
];

const getTranslation = (field, lang) => {
  if (!props.testimonial[field]) return '';
  if (typeof props.testimonial[field] === 'string') return props.testimonial[field];
  return props.testimonial[field][lang] || '';
};

const deleteTestimonial = () => {
  if (confirm(t('testimonials.confirmDelete'))) {
    router.delete(route('admin.testimonials.destroy', props.testimonial.id));
  }
};

const renderStars = (rating) => {
  return '★'.repeat(rating) + '☆'.repeat(5 - rating);
};
</script>

<template>
  <div>
    <!-- Content Header -->
    <div class="mb-4">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-[#1f2d3d]">{{ t('testimonials.info') }}</h1>
        </div>
        <nav class="mt-2 sm:mt-0">
          <ol class="flex items-center text-sm">
            <li><Link href="/admin/dashboard" class="text-[#007bff]">{{ t('common.home') }}</Link></li>
            <li class="mx-2 text-[#6c757d]">/</li>
            <li><Link href="/admin/testimonials" class="text-[#007bff]">{{ t('testimonials.title') }}</Link></li>
            <li class="mx-2 text-[#6c757d]">/</li>
            <li class="text-[#6c757d]">{{ t('common.view') }}</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <!-- Main Content -->
      <div class="lg:col-span-2">
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
            <div v-for="tab in tabs" :key="tab.key" v-show="activeTab === tab.key" class="space-y-4">
              <div>
                <label class="block text-xs font-semibold text-[#6c757d] uppercase mb-1">{{ t('testimonials.clientName') }}</label>
                <p class="text-[#1f2d3d]">{{ getTranslation('client_name', tab.key) || '-' }}</p>
              </div>
              <div>
                <label class="block text-xs font-semibold text-[#6c757d] uppercase mb-1">{{ t('testimonials.clientRole') }}</label>
                <p class="text-[#1f2d3d]">{{ getTranslation('client_role', tab.key) || '-' }}</p>
              </div>
              <div>
                <label class="block text-xs font-semibold text-[#6c757d] uppercase mb-1">{{ t('testimonials.comment') }}</label>
                <p class="text-[#1f2d3d] whitespace-pre-line">{{ getTranslation('comment', tab.key) || '-' }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="space-y-4">
        <!-- Details Card -->
        <div class="bg-white rounded shadow-sm">
          <div class="px-4 py-3 border-b border-gray-200">
            <h3 class="font-semibold text-[#1f2d3d] text-sm">{{ t('testimonials.info') }}</h3>
          </div>
          <div class="p-4 space-y-3">
            <div class="flex justify-between">
              <span class="text-sm text-[#6c757d]">{{ t('testimonials.rating') }}</span>
              <span class="text-[#ffc107]">{{ renderStars(testimonial.rating) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-[#6c757d]">{{ t('testimonials.sortOrder') }}</span>
              <span class="text-sm text-[#1f2d3d]">{{ testimonial.sort_order }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-[#6c757d]">{{ t('common.status') }}</span>
              <span
                v-if="testimonial.status"
                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded bg-[#d4edda] text-[#155724]"
              >
                {{ t('common.active') }}
              </span>
              <span
                v-else
                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded bg-[#f8d7da] text-[#721c24]"
              >
                {{ t('common.inactive') }}
              </span>
            </div>
          </div>
        </div>

        <!-- Actions Card -->
        <div class="bg-white rounded shadow-sm">
          <div class="px-4 py-3 border-b border-gray-200">
            <h3 class="font-semibold text-[#1f2d3d] text-sm">{{ t('common.actions') }}</h3>
          </div>
          <div class="p-4 space-y-2">
            <Link
              :href="route('admin.testimonials.edit', testimonial.id)"
              class="w-full inline-flex items-center justify-center px-4 py-2 bg-[#ffc107] text-[#1f2d3d] text-sm font-medium rounded hover:bg-[#e0a800] transition"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
              {{ t('common.edit') }}
            </Link>
            <button
              @click="deleteTestimonial"
              class="w-full inline-flex items-center justify-center px-4 py-2 bg-[#dc3545] text-white text-sm font-medium rounded hover:bg-[#c82333] transition"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
              </svg>
              {{ t('common.delete') }}
            </button>
            <Link
              href="/admin/testimonials"
              class="w-full inline-flex items-center justify-center px-4 py-2 bg-[#6c757d] text-white text-sm font-medium rounded hover:bg-[#5a6268] transition"
            >
              {{ t('common.back') }}
            </Link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
