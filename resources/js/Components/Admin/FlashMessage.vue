<script setup>
import { ref, watch, onMounted } from 'vue';

const props = defineProps({
  success: String,
  error: String,
  duration: {
    type: Number,
    default: 5000,
  },
});

const show = ref(true);

let timeout;

const autoHide = () => {
  timeout = setTimeout(() => {
    show.value = false;
  }, props.duration);
};

watch(
  () => [props.success, props.error],
  () => {
    show.value = true;
    clearTimeout(timeout);
    autoHide();
  }
);

onMounted(() => {
  if (props.success || props.error) {
    autoHide();
  }
});
</script>

<template>
  <Transition
    enter-active-class="transition ease-out duration-300"
    enter-from-class="opacity-0 translate-y-4"
    enter-to-class="opacity-100 translate-y-0"
    leave-active-class="transition ease-in duration-200"
    leave-from-class="opacity-100 translate-y-0"
    leave-to-class="opacity-0 translate-y-4"
  >
    <div
      v-if="show && (success || error)"
      :class="[
        'px-4 py-3 rounded-lg mb-6 flex items-center justify-between',
        success
          ? 'bg-green-50 text-green-800 border border-green-200'
          : 'bg-red-50 text-red-800 border border-red-200'
      ]"
    >
      <div class="flex items-center gap-2">
        <span class="text-lg font-semibold">{{ success ? '✓' : '❌' }}</span>
        <span>{{ success || error }}</span>
      </div>

      <button
        @click="show = false"
        class="text-gray-500 hover:text-gray-700 transition text-lg"
      >
        ✕
      </button>
    </div>
  </Transition>
</template>
