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
  <div class="min-h-screen bg-[#f4f6f9]">
    <!-- Sidebar -->
    <Sidebar />

    <!-- Main Content Wrapper -->
    <div class="ml-[250px] min-h-screen flex flex-col">
      <!-- Header/Navbar -->
      <Header :user="auth.user" />

      <!-- Main Content -->
      <main class="flex-1 p-4">
        <!-- Flash Messages -->
        <FlashMessage
          v-if="flash.success || flash.error"
          :success="flash.success"
          :error="flash.error"
        />

        <!-- Page Content -->
        <slot />
      </main>

      <!-- Footer -->
      <footer class="py-4 px-6 bg-white border-t border-gray-200 text-center text-sm text-[#6c757d]">
        <strong>Copyright &copy; 2024 <a href="#" class="text-[#007bff]">HomeMessage</a>.</strong>
        Barcha huquqlar himoyalangan.
        <span class="float-right hidden sm:inline">Version 1.0.0</span>
      </footer>
    </div>
  </div>
</template>
