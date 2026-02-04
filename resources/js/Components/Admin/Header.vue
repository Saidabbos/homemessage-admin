<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';

defineProps({
  user: Object,
});

const showDropdown = ref(false);

const logout = () => {
  router.post(route('admin.logout'));
};
</script>

<template>
  <header class="bg-white p-8 shadow-md flex justify-between items-center">
    <h2 class="text-2xl font-bold text-gray-800">
      <slot />
    </h2>

    <div class="flex items-center gap-6">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-purple-700 text-white flex items-center justify-center font-bold">
          {{ user?.avatar }}
        </div>
        <div v-if="user" class="text-sm">
          <div class="font-semibold text-gray-700">{{ user.name }}</div>
          <div class="text-gray-500">{{ user.role }}</div>
        </div>
      </div>

      <div class="relative">
        <button
          @click="showDropdown = !showDropdown"
          class="text-2xl text-gray-600 hover:text-gray-800 transition"
        >
          ⋮
        </button>

        <div
          v-if="showDropdown"
          class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
        >
          <Link href="#" class="block px-4 py-2 hover:bg-gray-50 border-b">👤 Profil</Link>
          <Link href="#" class="block px-4 py-2 hover:bg-gray-50 border-b">⚙️ Sozlamalar</Link>
          <button
            @click="logout"
            class="w-full text-left px-4 py-2 hover:bg-gray-50 text-red-600 font-medium"
          >
            🚪 Chiqish
          </button>
        </div>
      </div>
    </div>
  </header>
</template>
