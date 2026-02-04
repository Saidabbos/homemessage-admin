<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Sidebar from '@/Components/Admin/Sidebar.vue';
import Header from '@/Components/Admin/Header.vue';
import FlashMessage from '@/Components/Admin/FlashMessage.vue';

const page = usePage();
const auth = computed(() => page.props.auth);
const flash = computed(() => page.props.flash);
</script>

<template>
  <div class="flex min-h-screen bg-gray-50">
    <Sidebar />

    <main class="flex-1 ml-[280px]">
      <Header :user="auth.user" />

      <div class="p-8">
        <FlashMessage
          v-if="flash.success || flash.error"
          :success="flash.success"
          :error="flash.error"
        />

        <slot />
      </div>
    </main>
  </div>
</template>
