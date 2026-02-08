<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900">
    <!-- Header -->
    <div class="bg-purple-700 text-white py-8">
      <div class="max-w-2xl mx-auto px-4">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold">{{ t('customer.welcome') }}, {{ customer.name }}!</h1>
            <p class="text-purple-200 mt-1">Home Massage</p>
          </div>
          <div class="text-right">
            <p class="text-sm text-purple-200">{{ t('customer.memberSince') }}</p>
            <p class="font-semibold">{{ formattedDate }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-2xl mx-auto px-4 py-8">
      <!-- Profile Card -->
      <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-900 mb-6">{{ t('customer.profile') }}</h2>

        <div class="space-y-4">
          <!-- Name -->
          <div class="flex items-center justify-between pb-4 border-b border-gray-200">
            <div>
              <p class="text-sm text-gray-600">{{ t('common.name') }}</p>
              <p class="text-lg font-semibold text-gray-900">{{ customer.name }}</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
              <span class="text-xl font-bold text-purple-600">{{ customer.name.charAt(0) }}</span>
            </div>
          </div>

          <!-- Phone -->
          <div class="flex items-center justify-between pb-4 border-b border-gray-200">
            <div>
              <p class="text-sm text-gray-600">{{ t('customer.phone') }}</p>
              <p class="text-lg font-semibold text-gray-900">{{ customer.phone }}</p>
            </div>
            <svg class="w-6 h-6 text-green-500" fill="currentColor" viewBox="0 0 20 20">
              <path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
            </svg>
          </div>

          <!-- Language -->
          <div class="flex items-center justify-between pb-4 border-b border-gray-200">
            <div>
              <p class="text-sm text-gray-600">{{ t('customer.locale') }}</p>
              <p class="text-lg font-semibold text-gray-900">{{ localeDisplay }}</p>
            </div>
          </div>

          <!-- Status -->
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600">{{ t('customer.status') }}</p>
              <p class="text-lg font-semibold text-gray-900">
                <span v-if="customer.status" class="text-green-600">{{ t('common.active') }}</span>
                <span v-else class="text-red-600">{{ t('common.inactive') }}</span>
              </p>
            </div>
            <div :class="['w-3 h-3 rounded-full', customer.status ? 'bg-green-500' : 'bg-red-500']"/>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="grid grid-cols-2 gap-4 mb-6">
        <!-- Book Service Button -->
        <button class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-4 px-4 rounded-lg transition-all flex items-center justify-center gap-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m0 0h6"/>
          </svg>
          {{ t('orders.book') }}
        </button>

        <!-- View Orders Button -->
        <button class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-4 rounded-lg transition-all flex items-center justify-center gap-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          {{ t('common.orders') }}
        </button>
      </div>

      <!-- Coming Soon Section -->
      <div class="bg-gradient-to-r from-purple-50 to-blue-50 border-2 border-purple-200 rounded-2xl p-6 mb-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">{{ t('common.coming_soon') }}</h3>
        <ul class="space-y-2 text-gray-700">
          <li class="flex items-center gap-2">
            <span class="text-purple-600">•</span>
            Адреса бошқаришини қўлга ол
          </li>
          <li class="flex items-center gap-2">
            <span class="text-purple-600">•</span>
            Buyurtma tarixini ko'ring
          </li>
          <li class="flex items-center gap-2">
            <span class="text-purple-600">•</span>
            Профил маълумотларини таҳрирла
          </li>
        </ul>
      </div>

      <!-- Logout Button -->
      <form :action="route('customer.logout')" method="POST">
        <input type="hidden" name="_token" :value="csrfToken"/>
        <button
          type="submit"
          class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg transition-all flex items-center justify-center gap-2"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
          </svg>
          {{ t('customer.logout') }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { usePage } from '@inertiajs/vue3'

const { t } = useI18n()
const page = usePage()

const props = defineProps({
  customer: {
    type: Object,
    required: true,
  },
})

const csrfToken = computed(() => page.props.csrf_token)

const formattedDate = computed(() => {
  const date = new Date(props.customer.created_at)
  return date.toLocaleDateString()
})

const localeDisplay = computed(() => {
  const locales = {
    uz: 'O\'zbekcha',
    ru: 'Русский',
    en: 'English',
  }
  return locales[props.customer.locale] || props.customer.locale
})
</script>

<style scoped>
button:active {
  transform: scale(0.98);
}
</style>
