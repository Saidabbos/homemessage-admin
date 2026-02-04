<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/UI/Button.vue';

defineOptions({ layout: AdminLayout });

defineProps({
  serviceType: Object,
});

// Helper to get translations
const getTranslation = (key, locale) => {
  return serviceType[locale]?.[key] || '-';
};

const serviceTypeName = computed(() => {
  return serviceType.uz?.name || serviceType.name || 'Massage Type';
});

const deleteServiceType = () => {
  if (confirm('Haqiqatan ham bu massage turini o\'chirmoqchimisiz?')) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = route('admin.service-types.destroy', serviceType.id);
    form.innerHTML = `
      <input type="hidden" name="_token" value="${document.querySelector('meta[name=csrf-token]').content}">
      <input type="hidden" name="_method" value="DELETE">
    `;
    document.body.appendChild(form);
    form.submit();
  }
};
</script>

<template>
  <div class="max-w-4xl mx-auto">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">💆 {{ serviceTypeName }}</h1>

    <!-- Header with Image and Basic Info -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-8">
        <div class="md:col-span-1">
          <img
            :src="serviceType.image_url"
            :alt="serviceTypeName"
            class="w-full rounded-lg border border-gray-200 object-cover"
          />
        </div>

        <div class="md:col-span-2">
          <div class="space-y-4">
            <!-- Price -->
            <div class="border-b pb-4">
              <p class="text-gray-600 text-sm font-semibold uppercase">💰 Narxi</p>
              <p class="text-3xl font-bold text-gray-800 mt-1">
                {{ serviceType.price.toLocaleString() }} so'm
              </p>
            </div>

            <!-- Duration -->
            <div class="border-b pb-4">
              <p class="text-gray-600 text-sm font-semibold uppercase">⏱️ Davomiyligi</p>
              <p class="text-2xl font-bold text-gray-800 mt-1">{{ serviceType.duration }} daqiqa</p>
            </div>

            <!-- Slug -->
            <div class="border-b pb-4">
              <p class="text-gray-600 text-sm font-semibold uppercase">🔗 Slug</p>
              <p class="font-mono text-gray-800 mt-1 text-sm">{{ serviceType.slug }}</p>
            </div>

            <!-- Status -->
            <div>
              <p class="text-gray-600 text-sm font-semibold uppercase">📊 Status</p>
              <div class="mt-1">
                <span
                  v-if="serviceType.status"
                  class="inline-block bg-green-100 text-green-800 px-4 py-2 rounded-full text-sm font-semibold"
                >
                  ✓ Faol
                </span>
                <span v-else class="inline-block bg-red-100 text-red-800 px-4 py-2 rounded-full text-sm font-semibold">
                  ✗ Nofaol
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Translations Section -->
    <div class="space-y-6">
      <h2 class="text-2xl font-bold text-gray-800">🌍 Tarjimalar</h2>

      <!-- English Translation -->
      <div class="bg-white rounded-lg shadow-md p-8 border-l-4 border-blue-500">
        <div class="flex items-center gap-3 mb-6">
          <span class="text-3xl">🇬🇧</span>
          <h3 class="text-xl font-semibold text-gray-800">English</h3>
        </div>

        <div>
          <div class="mb-4">
            <p class="text-gray-600 text-sm font-semibold uppercase mb-2">Nomi</p>
            <p class="text-lg font-semibold text-gray-800">{{ getTranslation('name', 'en') }}</p>
          </div>

          <div v-if="getTranslation('description', 'en') !== '-'">
            <p class="text-gray-600 text-sm font-semibold uppercase mb-2">Tavsifi</p>
            <p class="text-gray-700 leading-relaxed">{{ getTranslation('description', 'en') }}</p>
          </div>
        </div>
      </div>

      <!-- Uzbek Translation -->
      <div class="bg-white rounded-lg shadow-md p-8 border-l-4 border-green-500">
        <div class="flex items-center gap-3 mb-6">
          <span class="text-3xl">🇺🇿</span>
          <h3 class="text-xl font-semibold text-gray-800">O'zbek</h3>
        </div>

        <div>
          <div class="mb-4">
            <p class="text-gray-600 text-sm font-semibold uppercase mb-2">Nomi</p>
            <p class="text-lg font-semibold text-gray-800">{{ getTranslation('name', 'uz') }}</p>
          </div>

          <div v-if="getTranslation('description', 'uz') !== '-'">
            <p class="text-gray-600 text-sm font-semibold uppercase mb-2">Tavsifi</p>
            <p class="text-gray-700 leading-relaxed">{{ getTranslation('description', 'uz') }}</p>
          </div>
        </div>
      </div>

      <!-- Russian Translation -->
      <div class="bg-white rounded-lg shadow-md p-8 border-l-4 border-red-500">
        <div class="flex items-center gap-3 mb-6">
          <span class="text-3xl">🇷🇺</span>
          <h3 class="text-xl font-semibold text-gray-800">Русский</h3>
        </div>

        <div>
          <div class="mb-4">
            <p class="text-gray-600 text-sm font-semibold uppercase mb-2">Название</p>
            <p class="text-lg font-semibold text-gray-800">{{ getTranslation('name', 'ru') }}</p>
          </div>

          <div v-if="getTranslation('description', 'ru') !== '-'">
            <p class="text-gray-600 text-sm font-semibold uppercase mb-2">Описание</p>
            <p class="text-gray-700 leading-relaxed">{{ getTranslation('description', 'ru') }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Metadata -->
    <div class="bg-gray-50 rounded-lg p-6 mt-8 text-sm text-gray-600">
      <div class="grid grid-cols-2 gap-4">
        <div>
          <p class="font-semibold text-gray-700">Yaratilgan</p>
          <p>{{ new Date(serviceType.created_at).toLocaleDateString('uz-UZ') }}</p>
        </div>
        <div>
          <p class="font-semibold text-gray-700">Oxirgi yangilash</p>
          <p>{{ new Date(serviceType.updated_at).toLocaleDateString('uz-UZ') }}</p>
        </div>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex gap-4 mt-8">
      <Link
        :href="route('admin.service-types.edit', serviceType.id)"
        class="inline-block bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-2 rounded-lg font-semibold transition"
      >
        ✏️ Tahrir Qilish
      </Link>

      <Link
        href="/admin/service-types"
        class="inline-block bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-semibold transition"
      >
        ← Orqaga
      </Link>

      <button
        @click="deleteServiceType"
        class="ml-auto bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-lg font-semibold transition"
      >
        🗑️ O'chirish
      </button>
    </div>
  </div>
</template>
