<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  links: Array,
});
</script>

<template>
  <div class="flex justify-center gap-2 mt-8">
    <Link
      v-for="(link, index) in links"
      :key="index"
      :href="link.url"
      :class="[
        'px-4 py-2 border rounded-lg transition text-sm font-medium',
        link.active
          ? 'bg-purple-500 text-white border-purple-500'
          : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50',
        !link.url ? 'opacity-50 cursor-not-allowed' : ''
      ]"
      :disabled="!link.url"
      v-html="link.label"
    />
  </div>
</template>
