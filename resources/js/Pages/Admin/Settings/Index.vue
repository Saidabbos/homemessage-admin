<script setup>
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();

const props = defineProps({
  settings: Object,
});

const activeTab = ref('general');
const activeLanguage = ref('uz');

const languageTabs = [
  { key: 'uz', label: "O'zbek", flag: '🇺🇿' },
  { key: 'ru', label: 'Русский', flag: '🇷🇺' },
  { key: 'en', label: 'English', flag: '🇬🇧' },
];

const form = useForm({
  // General
  app_name: props.settings?.general?.app_name || '',
  company_phone: props.settings?.general?.company_phone || '',
  company_email: props.settings?.general?.company_email || '',
  company_address: props.settings?.general?.company_address || '',
  working_hours_start: props.settings?.general?.working_hours_start || '09:00',
  working_hours_end: props.settings?.general?.working_hours_end || '21:00',
  // Booking
  min_booking_hours: props.settings?.booking?.min_booking_hours || 2,
  max_booking_days: props.settings?.booking?.max_booking_days || 30,
  cancellation_hours: props.settings?.booking?.cancellation_hours || 1,
  auto_confirm_booking: props.settings?.booking?.auto_confirm_booking || false,
  // Social
  telegram_link: props.settings?.social?.telegram_link || '',
  instagram_link: props.settings?.social?.instagram_link || '',
  facebook_link: props.settings?.social?.facebook_link || '',
  // Hero Section - Translatable
  uz: {
    hero_title: props.settings?.hero?.hero_title?.uz || '',
    hero_subtitle: props.settings?.hero?.hero_subtitle?.uz || '',
  },
  ru: {
    hero_title: props.settings?.hero?.hero_title?.ru || '',
    hero_subtitle: props.settings?.hero?.hero_subtitle?.ru || '',
  },
  en: {
    hero_title: props.settings?.hero?.hero_title?.en || '',
    hero_subtitle: props.settings?.hero?.hero_subtitle?.en || '',
  },
  // Hero Section - Non-translatable
  hero_badge: props.settings?.hero?.hero_badge || '',
  hero_cta_text: props.settings?.hero?.hero_cta_text || '',
  hero_view_services_text: props.settings?.hero?.hero_view_services_text || '',
  hero_image: null,
  hero_image_preview: props.settings?.hero?.hero_image ? `/storage/${props.settings.hero.hero_image}` : null,
});

const handleImageChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.hero_image = file;
    const reader = new FileReader();
    reader.onload = (event) => {
      form.hero_image_preview = event.target.result;
    };
    reader.readAsDataURL(file);
  }
};

const submit = () => {
  form.put(route('admin.settings.update'), {
    forceFormData: true,
  });
};

const tabs = [
  { id: 'general', label: 'settings.general' },
  { id: 'booking', label: 'settings.booking' },
  { id: 'social', label: 'settings.social' },
  { id: 'hero', label: 'settings.hero' },
];
</script>

<template>
  <div>
    <!-- Content Header -->
    <div class="mb-4">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-[#1f2d3d]">{{ t('settings.title') }}</h1>
          <p class="text-sm text-[#6c757d] mt-1">{{ t('settings.subtitle') }}</p>
        </div>
        <nav class="mt-2 sm:mt-0">
          <ol class="flex items-center text-sm">
            <li><Link href="/admin/dashboard" class="text-[#007bff]">{{ t('common.home') }}</Link></li>
            <li class="mx-2 text-[#6c757d]">/</li>
            <li class="text-[#6c757d]">{{ t('settings.title') }}</li>
          </ol>
        </nav>
      </div>
    </div>

    <form @submit.prevent="submit">
      <div class="bg-white rounded shadow-sm">
        <!-- Tabs -->
        <div class="border-b border-gray-200">
          <nav class="flex -mb-px">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              type="button"
              @click="activeTab = tab.id"
              :class="[
                'px-6 py-3 text-sm font-medium border-b-2 transition-colors',
                activeTab === tab.id
                  ? 'border-[#007bff] text-[#007bff]'
                  : 'border-transparent text-[#6c757d] hover:text-[#1f2d3d] hover:border-gray-300'
              ]"
            >
              {{ t(tab.label) }}
            </button>
          </nav>
        </div>

        <!-- General Settings -->
        <div v-show="activeTab === 'general'" class="p-6 space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- App Name -->
            <div>
              <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
                {{ t('settings.appName') }}
              </label>
              <input
                type="text"
                v-model="form.app_name"
                class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm"
                :placeholder="t('settings.enterAppName')"
              />
              <div v-if="form.errors.app_name" class="text-[#dc3545] text-xs mt-1">{{ form.errors.app_name }}</div>
            </div>

            <!-- Company Phone -->
            <div>
              <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
                {{ t('settings.companyPhone') }}
              </label>
              <input
                type="text"
                v-model="form.company_phone"
                class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm"
                placeholder="+998 90 123 45 67"
              />
              <div v-if="form.errors.company_phone" class="text-[#dc3545] text-xs mt-1">{{ form.errors.company_phone }}</div>
            </div>

            <!-- Company Email -->
            <div>
              <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
                {{ t('settings.companyEmail') }}
              </label>
              <input
                type="email"
                v-model="form.company_email"
                class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm"
                placeholder="info@example.com"
              />
              <div v-if="form.errors.company_email" class="text-[#dc3545] text-xs mt-1">{{ form.errors.company_email }}</div>
            </div>

            <!-- Company Address -->
            <div>
              <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
                {{ t('settings.companyAddress') }}
              </label>
              <input
                type="text"
                v-model="form.company_address"
                class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm"
                :placeholder="t('settings.enterAddress')"
              />
              <div v-if="form.errors.company_address" class="text-[#dc3545] text-xs mt-1">{{ form.errors.company_address }}</div>
            </div>

            <!-- Working Hours Start -->
            <div>
              <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
                {{ t('settings.workingHoursStart') }}
              </label>
              <input
                type="time"
                v-model="form.working_hours_start"
                class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm"
              />
              <div v-if="form.errors.working_hours_start" class="text-[#dc3545] text-xs mt-1">{{ form.errors.working_hours_start }}</div>
            </div>

            <!-- Working Hours End -->
            <div>
              <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
                {{ t('settings.workingHoursEnd') }}
              </label>
              <input
                type="time"
                v-model="form.working_hours_end"
                class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm"
              />
              <div v-if="form.errors.working_hours_end" class="text-[#dc3545] text-xs mt-1">{{ form.errors.working_hours_end }}</div>
            </div>
          </div>
        </div>

        <!-- Booking Settings -->
        <div v-show="activeTab === 'booking'" class="p-6 space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Min Booking Hours -->
            <div>
              <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
                {{ t('settings.minBookingHours') }}
              </label>
              <input
                type="number"
                v-model="form.min_booking_hours"
                min="1"
                max="72"
                class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm"
              />
              <p class="text-xs text-[#6c757d] mt-1">{{ t('settings.minBookingHoursHint') }}</p>
              <div v-if="form.errors.min_booking_hours" class="text-[#dc3545] text-xs mt-1">{{ form.errors.min_booking_hours }}</div>
            </div>

            <!-- Max Booking Days -->
            <div>
              <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
                {{ t('settings.maxBookingDays') }}
              </label>
              <input
                type="number"
                v-model="form.max_booking_days"
                min="1"
                max="90"
                class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm"
              />
              <p class="text-xs text-[#6c757d] mt-1">{{ t('settings.maxBookingDaysHint') }}</p>
              <div v-if="form.errors.max_booking_days" class="text-[#dc3545] text-xs mt-1">{{ form.errors.max_booking_days }}</div>
            </div>

            <!-- Cancellation Hours -->
            <div>
              <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
                {{ t('settings.cancellationHours') }}
              </label>
              <input
                type="number"
                v-model="form.cancellation_hours"
                min="0"
                max="48"
                class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm"
              />
              <p class="text-xs text-[#6c757d] mt-1">{{ t('settings.cancellationHoursHint') }}</p>
              <div v-if="form.errors.cancellation_hours" class="text-[#dc3545] text-xs mt-1">{{ form.errors.cancellation_hours }}</div>
            </div>

            <!-- Auto Confirm Booking -->
            <div class="flex items-start pt-6">
              <label class="flex items-center cursor-pointer">
                <input
                  type="checkbox"
                  v-model="form.auto_confirm_booking"
                  class="w-4 h-4 text-[#007bff] border-gray-300 rounded focus:ring-[#007bff]"
                />
                <span class="ml-2 text-sm text-[#1f2d3d]">{{ t('settings.autoConfirmBooking') }}</span>
              </label>
            </div>
          </div>
        </div>

        <!-- Social Settings -->
        <div v-show="activeTab === 'social'" class="p-6 space-y-4">
          <div class="grid grid-cols-1 gap-4">
            <!-- Telegram Link -->
            <div>
              <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
                <span class="inline-flex items-center">
                  <svg class="w-4 h-4 mr-2 text-[#0088cc]" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M11.944 0A12 12 0 1 0 24 12.056A12.014 12.014 0 0 0 11.944 0Zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472c-.18 1.898-.96 6.504-1.36 8.629c-.168.9-.499 1.201-.82 1.23c-.696.065-1.225-.46-1.9-.902c-1.056-.693-1.653-1.124-2.678-1.8c-1.185-.78-.417-1.21.258-1.91c.177-.184 3.247-2.977 3.307-3.23c.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345c-.48.33-.913.49-1.302.48c-.428-.008-1.252-.241-1.865-.44c-.752-.245-1.349-.374-1.297-.789c.027-.216.325-.437.893-.663c3.498-1.524 5.83-2.529 6.998-3.014c3.332-1.386 4.025-1.627 4.476-1.635Z"/>
                  </svg>
                  Telegram
                </span>
              </label>
              <input
                type="url"
                v-model="form.telegram_link"
                class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm"
                placeholder="https://t.me/username"
              />
              <div v-if="form.errors.telegram_link" class="text-[#dc3545] text-xs mt-1">{{ form.errors.telegram_link }}</div>
            </div>

            <!-- Instagram Link -->
            <div>
              <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
                <span class="inline-flex items-center">
                  <svg class="w-4 h-4 mr-2 text-[#E4405F]" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/>
                  </svg>
                  Instagram
                </span>
              </label>
              <input
                type="url"
                v-model="form.instagram_link"
                class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm"
                placeholder="https://instagram.com/username"
              />
              <div v-if="form.errors.instagram_link" class="text-[#dc3545] text-xs mt-1">{{ form.errors.instagram_link }}</div>
            </div>

            <!-- Facebook Link -->
            <div>
              <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
                <span class="inline-flex items-center">
                  <svg class="w-4 h-4 mr-2 text-[#1877F2]" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                  </svg>
                  Facebook
                </span>
              </label>
              <input
                type="url"
                v-model="form.facebook_link"
                class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm"
                placeholder="https://facebook.com/pagename"
              />
              <div v-if="form.errors.facebook_link" class="text-[#dc3545] text-xs mt-1">{{ form.errors.facebook_link }}</div>
            </div>
          </div>
        </div>

        <!-- Hero Section Settings -->
        <div v-show="activeTab === 'hero'" class="p-6 space-y-6">
          <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded text-sm text-[#1f2d3d]">
            <p class="font-medium mb-1">{{ t('settings.heroInfo') }}</p>
            <p class="text-[#6c757d]">{{ t('settings.heroInfoText') }}</p>
          </div>

          <!-- Hero Image Upload -->
          <div class="border border-gray-300 rounded-lg p-6 bg-gray-50">
            <label class="block text-sm font-medium text-[#1f2d3d] mb-3">
              {{ t('settings.heroImage') || 'Hero Background Image' }}
            </label>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Image Preview -->
              <div>
                <div v-if="form.hero_image_preview" class="relative overflow-hidden rounded-lg border border-gray-300 bg-white">
                  <img :src="form.hero_image_preview" alt="Hero preview" class="w-full h-64 object-cover" />
                  <button
                    type="button"
                    @click="form.hero_image = null; form.hero_image_preview = null"
                    class="absolute top-2 right-2 bg-red-500 text-white px-3 py-1 rounded text-xs font-medium hover:bg-red-600"
                  >
                    O'chirish
                  </button>
                </div>
                <div v-else class="rounded-lg border-2 border-dashed border-gray-300 bg-gray-100 h-64 flex items-center justify-center text-[#6c757d]">
                  Rasm tanlanmagan
                </div>
              </div>

              <!-- Upload Section -->
              <div>
                <label class="block mb-3">
                  <div class="border-2 border-dashed border-[#007bff] rounded-lg p-6 bg-blue-50 text-center cursor-pointer hover:bg-blue-100 transition">
                    <svg class="w-8 h-8 mx-auto mb-2 text-[#007bff]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <p class="font-medium text-[#1f2d3d] text-sm">{{ t('settings.clickOrDrag') || 'Rasm tanlang yoki suriting' }}</p>
                    <p class="text-xs text-[#6c757d] mt-1">PNG, JPG, WebP. Max 5MB</p>
                  </div>
                  <input
                    type="file"
                    @change="handleImageChange"
                    accept="image/*"
                    class="hidden"
                  />
                </label>
                <p class="text-xs text-[#6c757d] mt-3">{{ t('settings.heroImageHint') || 'Rasm yuklanmasa, joriy default rasm qoʻllaniladi' }}</p>
              </div>
            </div>
            <div v-if="form.errors.hero_image" class="text-[#dc3545] text-xs mt-2">{{ form.errors.hero_image }}</div>
          </div>

          <!-- Translatable Fields with Language Tabs -->
          <div class="bg-white rounded shadow-sm border border-gray-300">
            <div class="px-4 py-3 border-b border-gray-200 bg-[#17a2b8] rounded-t">
              <h3 class="font-semibold text-white flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                </svg>
                {{ t('translations.title') }}
              </h3>
            </div>

            <!-- Language Tabs -->
            <div class="border-b border-gray-200">
              <ul class="flex flex-wrap -mb-px">
                <li v-for="tab in languageTabs" :key="tab.key" class="mr-1">
                  <button
                    type="button"
                    @click="activeLanguage = tab.key"
                    :class="[
                      'inline-flex items-center gap-2 px-4 py-3 text-sm font-medium border-b-2 transition',
                      activeLanguage === tab.key
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

            <!-- Uzbek -->
            <div v-show="activeLanguage === 'uz'" class="p-6 space-y-4">
              <div>
                <label class="block text-sm font-medium text-[#1f2d3d] mb-1">{{ t('settings.heroTitle') }}</label>
                <input
                  type="text"
                  v-model="form.uz.hero_title"
                  class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm"
                  placeholder="Premium massaj xizmati..."
                />
                <div v-if="form.errors['uz.hero_title']" class="text-[#dc3545] text-xs mt-1">{{ form.errors['uz.hero_title'] }}</div>
              </div>
              <div>
                <label class="block text-sm font-medium text-[#1f2d3d] mb-1">{{ t('settings.heroSubtitle') }}</label>
                <textarea
                  v-model="form.uz.hero_subtitle"
                  rows="3"
                  class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm"
                  placeholder="Uyingizga premium massaj xizmati..."
                ></textarea>
                <div v-if="form.errors['uz.hero_subtitle']" class="text-[#dc3545] text-xs mt-1">{{ form.errors['uz.hero_subtitle'] }}</div>
              </div>
            </div>

            <!-- Russian -->
            <div v-show="activeLanguage === 'ru'" class="p-6 space-y-4">
              <div>
                <label class="block text-sm font-medium text-[#1f2d3d] mb-1">{{ t('settings.heroTitle') }}</label>
                <input
                  type="text"
                  v-model="form.ru.hero_title"
                  class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm"
                  placeholder="Премиум массаж..."
                />
                <div v-if="form.errors['ru.hero_title']" class="text-[#dc3545] text-xs mt-1">{{ form.errors['ru.hero_title'] }}</div>
              </div>
              <div>
                <label class="block text-sm font-medium text-[#1f2d3d] mb-1">{{ t('settings.heroSubtitle') }}</label>
                <textarea
                  v-model="form.ru.hero_subtitle"
                  rows="3"
                  class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm"
                  placeholder="Премиум массаж в вашем доме..."
                ></textarea>
                <div v-if="form.errors['ru.hero_subtitle']" class="text-[#dc3545] text-xs mt-1">{{ form.errors['ru.hero_subtitle'] }}</div>
              </div>
            </div>

            <!-- English -->
            <div v-show="activeLanguage === 'en'" class="p-6 space-y-4">
              <div>
                <label class="block text-sm font-medium text-[#1f2d3d] mb-1">{{ t('settings.heroTitle') }}</label>
                <input
                  type="text"
                  v-model="form.en.hero_title"
                  class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm"
                  placeholder="Premium massage service..."
                />
                <div v-if="form.errors['en.hero_title']" class="text-[#dc3545] text-xs mt-1">{{ form.errors['en.hero_title'] }}</div>
              </div>
              <div>
                <label class="block text-sm font-medium text-[#1f2d3d] mb-1">{{ t('settings.heroSubtitle') }}</label>
                <textarea
                  v-model="form.en.hero_subtitle"
                  rows="3"
                  class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm"
                  placeholder="Premium massage service at your home..."
                ></textarea>
                <div v-if="form.errors['en.hero_subtitle']" class="text-[#dc3545] text-xs mt-1">{{ form.errors['en.hero_subtitle'] }}</div>
              </div>
            </div>
          </div>

          <!-- Non-translatable Fields -->
          <div class="grid grid-cols-1 gap-4">
            <!-- Hero Badge -->
            <div>
              <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
                {{ t('settings.heroBadge') }}
              </label>
              <input
                type="text"
                v-model="form.hero_badge"
                class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm"
                placeholder="✦ Premium xizmat"
              />
              <p class="text-xs text-[#6c757d] mt-1">{{ t('settings.heroBadgeHint') }}</p>
              <div v-if="form.errors.hero_badge" class="text-[#dc3545] text-xs mt-1">{{ form.errors.hero_badge }}</div>
            </div>

            <!-- Hero CTA Text -->
            <div>
              <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
                {{ t('settings.heroCtaText') }}
              </label>
              <input
                type="text"
                v-model="form.hero_cta_text"
                class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm"
                placeholder="Seansni Band Qiling"
              />
              <p class="text-xs text-[#6c757d] mt-1">{{ t('settings.heroCtaTextHint') }}</p>
              <div v-if="form.errors.hero_cta_text" class="text-[#dc3545] text-xs mt-1">{{ form.errors.hero_cta_text }}</div>
            </div>

            <!-- Hero View Services Text -->
            <div>
              <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
                {{ t('settings.heroViewServicesText') }}
              </label>
              <input
                type="text"
                v-model="form.hero_view_services_text"
                class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm"
                placeholder="Xizmatlarga o'tish"
              />
              <p class="text-xs text-[#6c757d] mt-1">{{ t('settings.heroViewServicesTextHint') }}</p>
              <div v-if="form.errors.hero_view_services_text" class="text-[#dc3545] text-xs mt-1">{{ form.errors.hero_view_services_text }}</div>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b">
          <button
            type="submit"
            :disabled="form.processing"
            class="inline-flex items-center px-4 py-2 bg-[#007bff] text-white text-sm font-medium rounded hover:bg-[#0069d9] transition disabled:opacity-50"
          >
            <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ form.processing ? t('common.saving') : t('common.save') }}
          </button>
        </div>
      </div>
    </form>
  </div>
</template>
