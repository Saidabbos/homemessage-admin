<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const flash = computed(() => page.props.flash);
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-purple-500 via-purple-600 to-purple-800">
    <div class="min-h-screen flex items-center justify-center p-4">
      <div class="w-full max-w-md">
        <slot />
      </div>
    </div>
  </div>
</template>
