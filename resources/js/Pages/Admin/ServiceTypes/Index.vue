<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/UI/Pagination.vue';

defineOptions({ layout: AdminLayout });

defineProps({
  serviceTypes: Object,
});

const deleteServiceType = (id) => {
  if (confirm('Haqiqatan ham bu massage turini o\'chirmoqchimisiz?')) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = route('admin.service-types.destroy', id);
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
  <div>
    <div class="flex justify-between items-center mb-8">
      <h1 class="text-3xl font-bold text-gray-800">💆 Massage Turlari</h1>
      <Link
        href="/admin/service-types/create"
        class="bg-purple-500 hover:bg-purple-600 text-white px-6 py-2 rounded-lg font-semibold transition"
      >
        ➕ Yangi Turl Qo'shish
      </Link>
    </div>

    <!-- Services Table -->
    <div v-if="serviceTypes.data.length > 0" class="bg-white rounded-lg shadow-md overflow-hidden">
      <table class="w-full">
        <thead class="bg-gray-100 border-b-2 border-gray-200">
          <tr>
            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Rasm</th>
            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Nomi (UZ)</th>
            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Davomiyligi</th>
            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Narxi</th>
            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Status</th>
            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Amallar</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="type in serviceTypes.data" :key="type.id" class="border-b hover:bg-gray-50 transition">
            <td class="px-6 py-4">
              <img
                :src="type.image_url"
                :alt="type.name"
                class="w-12 h-12 rounded-lg object-cover border border-gray-200"
              />
            </td>
            <td class="px-6 py-4">
              <strong class="text-gray-800">{{ type.getTranslation?.('name', 'uz') || type.name }}</strong>
              <br>
              <small class="text-gray-500">{{ type.slug }}</small>
            </td>
            <td class="px-6 py-4">
              <span class="text-gray-700">⏱️ {{ type.duration }} min</span>
            </td>
            <td class="px-6 py-4">
              <span class="text-gray-700">💰 {{ type.price.toLocaleString() }} so'm</span>
            </td>
            <td class="px-6 py-4">
              <span
                v-if="type.status"
                class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold"
              >
                ✓ Faol
              </span>
              <span v-else class="inline-block bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-semibold">
                ✗ Nofaol
              </span>
            </td>
            <td class="px-6 py-4">
              <div class="flex gap-2">
                <Link
                  :href="route('admin.service-types.show', type.id)"
                  class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm transition"
                >
                  Ko'rish
                </Link>
                <Link
                  :href="route('admin.service-types.edit', type.id)"
                  class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-sm transition"
                >
                  Tahrir
                </Link>
                <button
                  @click="deleteServiceType(type.id)"
                  class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm transition"
                >
                  O'chirish
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Empty State -->
    <div v-else class="bg-white rounded-lg shadow-md p-12 text-center">
      <div class="text-6xl mb-4">🏥</div>
      <h3 class="text-2xl font-bold text-gray-800 mb-2">Massage turlari yo'q</h3>
      <p class="text-gray-600 mb-6">Hozircha hech qanday massage turi yaratilmadi</p>
      <Link
        href="/admin/service-types/create"
        class="inline-block bg-purple-500 hover:bg-purple-600 text-white px-6 py-2 rounded-lg font-semibold transition"
      >
        ➕ Birinchi Turl Qo'shish
      </Link>
    </div>

    <!-- Pagination -->
    <Pagination v-if="serviceTypes.links.length > 0" :links="serviceTypes.links" />
  </div>
</template>
