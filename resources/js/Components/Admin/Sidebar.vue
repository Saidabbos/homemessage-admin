<script setup>
import { Link } from '@inertiajs/vue3';

const isActive = (routeName) => {
  return route().current(routeName + '*');
};
</script>

<template>
  <aside class="fixed left-0 h-screen w-[280px] bg-gradient-to-br from-purple-500 via-purple-600 to-purple-800 text-white p-5 shadow-lg overflow-y-auto">
    <div class="text-2xl font-bold mb-8 pb-5 border-b border-white/20">
      🏠 HomeMessage
    </div>

    <nav>
      <ul class="space-y-2">
        <li>
          <Link
            href="/admin/dashboard"
            :class="[
              'block px-4 py-3 rounded-lg transition-all duration-200',
              isActive('admin.dashboard')
                ? 'bg-white/30 text-white'
                : 'text-white/90 hover:bg-white/20'
            ]"
          >
            📊 Dashboard
          </Link>
        </li>

        <li>
          <Link
            href="/admin/service-types"
            :class="[
              'block px-4 py-3 rounded-lg transition-all duration-200',
              isActive('admin.service-types')
                ? 'bg-white/30 text-white'
                : 'text-white/90 hover:bg-white/20'
            ]"
          >
            💆 Massage Turlari
          </Link>
        </li>
      </ul>
    </nav>
  </aside>
</template>
