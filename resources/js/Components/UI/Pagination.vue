<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  links: Array,
});
</script>

<template>
  <div class="flex justify-center gap-2 mt-8">
    <template v-for="(link, index) in links" :key="index">
      <Link
        v-if="link.url"
        :href="link.url"
        :class="[
          'px-4 py-2 border rounded-lg transition text-sm font-medium',
          link.active
            ? 'bg-purple-500 text-white border-purple-500'
            : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
        ]"
        v-html="link.label"
      />
      <span
        v-else
        :class="[
          'px-4 py-2 border rounded-lg text-sm font-medium',
          'bg-gray-100 text-gray-400 border-gray-300 opacity-50 cursor-not-allowed'
        ]"
        v-html="link.label"
      />
    </template>
  </div>
</template>
