<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useI18n } from 'vue-i18n';

const { locale } = useI18n();
const showDropdown = ref(false);
const dropdownRef = ref(null);

const languages = [
  { code: 'uz', name: "O'zbekcha", flag: '🇺🇿' },
  { code: 'ru', name: 'Русский', flag: '🇷🇺' },
  { code: 'en', name: 'English', flag: '🇬🇧' },
];

const currentLanguage = computed(() => {
  return languages.find(lang => lang.code === locale.value) || languages[0];
});

const changeLanguage = (langCode) => {
  locale.value = langCode;
  localStorage.setItem('locale', langCode);
  showDropdown.value = false;
};

const handleClickOutside = (event) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    showDropdown.value = false;
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
  <div class="relative" ref="dropdownRef">
    <button
      @click="showDropdown = !showDropdown"
      class="flex items-center gap-2 px-3 py-1.5 text-gray-600 hover:bg-gray-100 rounded-lg transition"
    >
      <span class="text-lg">{{ currentLanguage.flag }}</span>
      <span class="hidden sm:block text-sm font-medium">{{ currentLanguage.code.toUpperCase() }}</span>
      <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
      </svg>
    </button>

    <!-- Language Dropdown -->
    <Transition
      enter-active-class="transition ease-out duration-100"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div
        v-if="showDropdown"
        class="absolute right-0 mt-2 w-44 bg-white rounded-lg shadow-lg border border-gray-200 z-50 overflow-hidden"
      >
        <div class="py-1">
          <button
            v-for="lang in languages"
            :key="lang.code"
            @click="changeLanguage(lang.code)"
            class="flex items-center gap-3 w-full px-4 py-2 text-sm hover:bg-gray-50 transition"
            :class="locale === lang.code ? 'bg-blue-50 text-[#007bff]' : 'text-gray-700'"
          >
            <span class="text-lg">{{ lang.flag }}</span>
            <span class="font-medium">{{ lang.name }}</span>
            <svg
              v-if="locale === lang.code"
              class="w-4 h-4 ml-auto text-[#007bff]"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
          </button>
        </div>
      </div>
    </Transition>
  </div>
</template>
